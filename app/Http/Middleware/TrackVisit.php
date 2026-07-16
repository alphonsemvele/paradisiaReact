<?php

namespace App\Http\Middleware;

use App\Models\Visit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class TrackVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Ne pas tracker les requêtes non-GET, AJAX, ou assets
        if (
            ! $request->isMethod('GET') ||
            $request->ajax() ||
            $request->wantsJson() ||
            $request->is('admin/*') ||
            $request->is('_debugbar/*') ||
            $request->is('horizon/*') ||
            $this->isAsset($request)
        ) {
            return $response;
        }

        // On capture ce dont on a besoin maintenant, mais tout le travail
        // (anti-doublon, géolocalisation HTTP, insertion) s'exécute APRÈS
        // l'envoi de la réponse : le tracking ne ralentit jamais la page.
        $userAgent = $request->userAgent();
        $sessionId = $request->session()->getId();
        $ip = $request->ip();
        $fullUrl = $request->fullUrl();
        $path = $request->path() ?: '/';
        $referer = $request->headers->get('referer');
        $user = Auth::user();

        app()->terminating(function () use ($userAgent, $sessionId, $ip, $fullUrl, $path, $referer, $user) {
            try {
                // Anti-doublon : même session + même page dans 30 min
                $alreadyVisited = Visit::where('session_id', $sessionId)
                    ->where('path', $path)
                    ->where('created_at', '>=', now()->subMinutes(30))
                    ->exists();

                if ($alreadyVisited) {
                    return;
                }

                // 🌍 Géolocalisation par IP (avec cache 24h)
                $geo = $this->getGeoFromIp($ip);

                Visit::create([
                    'ip_address' => $ip,
                    'session_id' => $sessionId,
                    'id_user' => $user?->id,
                    'url' => $fullUrl,
                    'path' => $path,
                    'referer' => $referer,
                    'user_agent' => mb_substr($userAgent ?? '', 0, 500),
                    'device_type' => $this->detectDevice($userAgent),
                    'browser' => $this->detectBrowser($userAgent),
                    'os' => $this->detectOS($userAgent),

                    // 🆕 Priorité : user > géo IP
                    'country' => $user?->country ?? $geo['country'] ?? null,
                    'country_code' => $user?->country_code ?? $geo['country_code'] ?? null,
                    'city' => $geo['city'] ?? null,
                ]);
            } catch (\Throwable $e) {
                \Log::warning('TrackVisit failed: ' . $e->getMessage());
            }
        });

        return $response;
    }

    /**
     * 🌍 Récupère pays/ville depuis l'IP via ip-api.com
     * - Cache 24h par IP (évite de spammer l'API)
     * - Skip les IP locales (127.0.0.1, 192.168.x.x...)
     */
    private function getGeoFromIp(string $ip): array
    {
        // IP locales / privées → pas de géo possible
        if ($this->isLocalIp($ip)) {
            return [
                'country' => 'Local',
                'country_code' => 'XX',
                'city' => 'Localhost',
            ];
        }

        // Cache 24h par IP
        return Cache::remember("geo:{$ip}", now()->addHours(24), function () use ($ip) {
            try {
                $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}", [
                    'fields' => 'status,country,countryCode,city',
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (($data['status'] ?? '') === 'success') {
                        return [
                            'country' => $data['country'] ?? null,
                            'country_code' => $data['countryCode'] ?? null,
                            'city' => $data['city'] ?? null,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                \Log::info("Geo lookup failed for IP {$ip}: " . $e->getMessage());
            }

            return [
                'country' => null,
                'country_code' => null,
                'city' => null,
            ];
        });
    }

    /**
     * Détecte si une IP est locale / privée
     */
    private function isLocalIp(string $ip): bool
    {
        return ! filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    private function isAsset(Request $request): bool
    {
        $path = $request->path();
        $extensions = [
            '.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg',
            '.ico', '.woff', '.woff2', '.ttf', '.map', '.webp',
        ];

        foreach ($extensions as $ext) {
            if (str_ends_with($path, $ext)) {
                return true;
            }
        }

        return str_starts_with($path, 'build/') ||
               str_starts_with($path, 'storage/') ||
               str_starts_with($path, 'uploads/');
    }

    private function detectDevice(?string $userAgent): string
    {
        if (! $userAgent) return 'unknown';

        $ua = strtolower($userAgent);

        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobile))/i', $ua)) {
            return 'tablet';
        }

        if (preg_match('/(mobile|iphone|ipod|android|blackberry|opera mini|opera mobi|webos)/i', $ua)) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function detectBrowser(?string $userAgent): string
    {
        if (! $userAgent) return 'Unknown';

        $browsers = [
            'Edge' => 'edg',
            'Chrome' => 'chrome',
            'Firefox' => 'firefox',
            'Safari' => 'safari',
            'Opera' => 'opera|opr',
            'Internet Explorer' => 'msie|trident',
        ];

        $ua = strtolower($userAgent);

        foreach ($browsers as $name => $pattern) {
            if (preg_match('/' . $pattern . '/i', $ua)) {
                return $name;
            }
        }

        return 'Other';
    }

    private function detectOS(?string $userAgent): string
    {
        if (! $userAgent) return 'Unknown';

        $oses = [
            'Windows' => 'windows',
            'macOS' => 'mac os|macintosh',
            'iOS' => 'iphone|ipad|ipod',
            'Android' => 'android',
            'Linux' => 'linux',
        ];

        $ua = strtolower($userAgent);

        foreach ($oses as $name => $pattern) {
            if (preg_match('/' . $pattern . '/i', $ua)) {
                return $name;
            }
        }

        return 'Other';
    }
}