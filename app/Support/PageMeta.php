<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Métadonnées de partage (Open Graph / Twitter Card) rendues côté serveur.
 *
 * Indispensable : les robots de WhatsApp, Facebook, Telegram… n'exécutent
 * pas le JavaScript. Ils ne voient que le HTML renvoyé par Laravel — pas ce
 * qu'Inertia affiche ensuite. Sans ces balises, un lien partagé n'affiche ni
 * titre, ni description, ni image.
 */
class PageMeta
{
    public const SITE_NAME = 'PARADISIA';

    /**
     * Construit le jeu de métadonnées d'une page.
     *
     * @param  string|null  $image  URL absolue de l'image d'aperçu
     */
    public static function make(
        string $title,
        ?string $description = null,
        ?string $image = null,
        ?string $url = null,
        string $type = 'website',
    ): array {
        $image = $image ?: self::defaultImage();

        return [
            'title' => $title,
            'description' => Str::limit(
                trim(preg_replace('/\s+/', ' ', (string) $description)) ?: self::defaultDescription(),
                200
            ),
            'image' => $image,
            'image_size' => self::imageSize($image),
            'url' => $url ?: url()->current(),
            'type' => $type,
            'site_name' => self::SITE_NAME,
        ];
    }

    /**
     * Dimensions réelles de l'image d'aperçu (les annoncer accélère le rendu
     * chez WhatsApp/Facebook ; les inventer déforme la vignette).
     *
     * @return array{width:int,height:int}|null
     */
    private static function imageSize(?string $imageUrl): ?array
    {
        if (! $imageUrl) {
            return null;
        }

        $path = ltrim((string) parse_url($imageUrl, PHP_URL_PATH), '/');
        $file = public_path($path);

        if (! is_file($file)) {
            return null;
        }

        $size = @getimagesize($file);

        return $size ? ['width' => $size[0], 'height' => $size[1]] : null;
    }

    /** Métadonnées d'une publication partagée. */
    public static function forPublication(array $publication, string $url): array
    {
        $author = $publication['user']['name'] ?? self::SITE_NAME;
        $text = trim((string) ($publication['text'] ?? ''));

        return self::make(
            title: $text !== ''
                ? Str::limit($text, 70)
                : "Publication de {$author}",
            description: $text !== '' ? $text : "Découvrez cette publication de {$author} sur ".self::SITE_NAME.'.',
            image: $publication['images'][0] ?? null,
            url: $url,
            type: 'article',
        );
    }

    public static function defaultDescription(): string
    {
        return 'PARADISIA — jus naturels d\'ananas produits au Cameroun, formations et points de vente.';
    }

    public static function defaultImage(): ?string
    {
        $candidates = ['images/products/compose.jpeg', 'background.jpg'];

        foreach ($candidates as $path) {
            if (file_exists(public_path($path))) {
                return asset($path);
            }
        }

        return null;
    }
}
