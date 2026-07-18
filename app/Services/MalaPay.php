<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Client de l'API Malapay v1.
 *
 * En attendant l'intégration des opérateurs mobile money par pays, les
 * paiements Paradisia passent par les portefeuilles Malapay : on vérifie la
 * devise et le solde, puis on débite. Si le portefeuille est vide ou dans une
 * autre devise, Malapay renvoie les représentants à contacter pour le créditer.
 */
class MalaPay
{
    public function __construct(
        private readonly ?string $baseUrl = null,
        private readonly ?string $cle = null,
    ) {}

    public function estConfigure(): bool
    {
        return filled($this->url()) && filled($this->clef());
    }

    /**
     * Pays disponibles et leur devise. Mis en cache : la liste ne change
     * pratiquement jamais et le formulaire de paiement s'ouvre instantanément.
     *
     * @return array<int, array{code:string,nom:string,drapeau:?string,devise:string,indicatif:?string}>
     */
    public function pays(): array
    {
        return Cache::remember('malapay.pays', now()->addDay(), function () {
            $reponse = $this->requete()->get($this->url().'/v1/pays');

            return $reponse->successful() ? ($reponse->json('data') ?? []) : [];
        });
    }

    /**
     * Vérifie un portefeuille avant paiement : existence, devise, solde.
     *
     * @return array{ok:bool, data?:array, code?:string, message?:string, representants?:array}
     */
    public function verifierPortefeuille(string $code, string $devise, float $montant, ?string $pays = null): array
    {
        return $this->appeler('post', '/v1/portefeuilles/verifier', [
            'code' => $code,
            'devise' => $devise,
            'montant' => $montant,
            'pays' => $pays,
        ]);
    }

    /**
     * Débite le portefeuille. La référence rend l'appel idempotent : en cas de
     * double soumission ou de reprise après incident, le client n'est pas
     * débité deux fois.
     *
     * @return array{ok:bool, data?:array, code?:string, message?:string, representants?:array}
     */
    public function debiter(string $code, float $montant, string $devise, string $reference, ?string $description = null): array
    {
        return $this->appeler('post', '/v1/portefeuilles/debiter', [
            'code' => $code,
            'montant' => $montant,
            'devise' => $devise,
            'reference' => $reference,
            'description' => $description,
        ]);
    }

    /** Représentants habilités à créditer un portefeuille. */
    public function representants(?string $pays = null): array
    {
        $resultat = $this->appeler('get', '/v1/representants', array_filter(['pays' => $pays]));

        return $resultat['ok'] ? ($resultat['data'] ?? []) : [];
    }

    /* ═══════════════════════ Interne ═══════════════════════ */

    /**
     * Enveloppe unique des appels : une panne réseau ou une erreur Malapay
     * ressort toujours sous la même forme, jamais en exception non gérée.
     */
    private function appeler(string $methode, string $chemin, array $donnees): array
    {
        if (! $this->estConfigure()) {
            return [
                'ok' => false,
                'code' => 'MALAPAY_NOT_CONFIGURED',
                'message' => 'Le paiement Malapay n\'est pas encore configuré sur ce site.',
            ];
        }

        try {
            $reponse = $this->requete()->{$methode}($this->url().$chemin, $donnees);
        } catch (ConnectionException $e) {
            Log::warning('Malapay injoignable : '.$e->getMessage());

            return [
                'ok' => false,
                'code' => 'MALAPAY_UNREACHABLE',
                'message' => 'Le service de paiement est momentanément injoignable. Réessayez dans un instant.',
            ];
        }

        $corps = $reponse->json() ?? [];

        if ($reponse->successful() && ($corps['success'] ?? false)) {
            return ['ok' => true, 'data' => $corps['data'] ?? []];
        }

        $erreur = $corps['error'] ?? [];

        return [
            'ok' => false,
            'code' => $erreur['code'] ?? 'MALAPAY_ERROR',
            'message' => $erreur['message'] ?? 'Le paiement a échoué. Réessayez ou contactez un représentant.',
            'representants' => $erreur['representants'] ?? [],
        ];
    }

    private function requete()
    {
        return Http::withToken($this->clef())
            ->acceptJson()
            ->timeout(config('services.malapay.timeout', 15))
            ->retry(2, 200, throw: false);
    }

    private function url(): ?string
    {
        return rtrim($this->baseUrl ?? (string) config('services.malapay.url'), '/') ?: null;
    }

    private function clef(): ?string
    {
        return $this->cle ?? config('services.malapay.key');
    }
}
