<?php

namespace App\Support;

use App\Services\ImageOptimizer;
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

    /** Nom tel qu'il s'affiche en titre d'aperçu de partage. */
    public const DISPLAY_NAME = 'Paradisia';

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
        ?string $ogTitle = null,
    ): array {
        $image = $image ?: self::defaultImage();

        // Vignette de partage 1200×630 compressée : format exact attendu par
        // WhatsApp et Facebook. Évite qu'une image lourde ou mal proportionnée
        // ne s'affiche pas dans l'aperçu.
        $vignette = self::vignettePartage($image);

        return [
            'title' => $title,
            // Titre de l'aperçu partagé, distinct du titre d'onglet : sur
            // WhatsApp/Facebook c'est la ligne en gras au-dessus du texte.
            'og_title' => $ogTitle ?: $title,
            'description' => Str::limit(
                trim(preg_replace('/\s+/', ' ', (string) $description)) ?: self::defaultDescription(),
                200
            ),
            'image' => $vignette ?? $image,
            'image_size' => $vignette ? ['width' => 1200, 'height' => 630] : self::imageSize($image),
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
    /**
     * Renvoie l'URL d'une vignette de partage 1200×630 dérivée de l'image
     * donnée, en la générant à la volée si elle n'existe pas encore. Retourne
     * null si l'image source est introuvable ou la génération échoue (on
     * retombe alors sur l'image d'origine).
     */
    private static function vignettePartage(?string $imageUrl): ?string
    {
        if (! $imageUrl) {
            return null;
        }

        $chemin = ltrim((string) parse_url($imageUrl, PHP_URL_PATH), '/');
        $source = public_path($chemin);

        if (! is_file($source)) {
            return null;
        }

        // Nom stable dérivé du contenu : régénéré si l'image source change.
        $cle = substr(md5($chemin.'|'.filemtime($source)), 0, 16);
        $relative = 'uploads/og/og_'.$cle.'.jpg';
        $destination = public_path($relative);

        if (is_file($destination) || ImageOptimizer::vignettePartage($source, $destination)) {
            return asset($relative);
        }

        return null;
    }

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

    /**
     * Métadonnées d'une publication partagée.
     *
     * Rendu voulu, identique à un lien Facebook dans WhatsApp : grande image,
     * « Paradisia » en gras, puis le texte de la publication. D'où og:title =
     * nom du site et og:description = texte du post — le titre de l'onglet,
     * lui, garde le texte pour rester utile en navigation et en référencement.
     */
    public static function forPublication(array $publication, string $url): array
    {
        $author = $publication['user']['name'] ?? self::DISPLAY_NAME;
        $text = trim((string) ($publication['text'] ?? ''));

        return self::make(
            title: $text !== ''
                ? Str::limit($text, 70)
                : "Publication de {$author}",
            description: $text !== ''
                ? $text
                : "Découvrez cette publication de {$author} sur ".self::DISPLAY_NAME.'.',
            image: $publication['images'][0] ?? null,
            url: $url,
            type: 'article',
            ogTitle: self::DISPLAY_NAME,
        );
    }

    /**
     * Métadonnées d'une formation partagée : grande image, titre de la
     * formation en gras, puis les conditions (prix, durée, session, mode)
     * suivies de la description. Le titre n'est pas repris dans la
     * description pour éviter le doublon dans l'aperçu.
     */
    public static function forFormation(array $formation, string $url): array
    {
        $titre = $formation['titre'] ?? 'Formation';

        $conditions = collect([
            ($formation['prix'] ?? 0) > 0 ? $formation['prix_formatte'] ?? null : null,
            $formation['duree'] ?? null,
            $formation['session'] ?? null,
            $formation['mode_label'] ?? null,
        ])->filter()->implode(' · ');

        $description = trim((string) ($formation['description'] ?? ''));

        return self::make(
            title: $titre.' — Formation '.self::DISPLAY_NAME,
            description: collect([$conditions, $description])->filter()->implode(' — '),
            image: $formation['image'] ?? null,
            url: $url,
            type: 'article',
            ogTitle: $titre,
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
