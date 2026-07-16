<?php

namespace App\Services;

/**
 * Optimise une image uploadée, en place, avec GD : redimensionne à 1600 px
 * max et ré-encode (JPEG/WebP qualité 80, PNG compressé). Une photo de
 * téléphone de 8 Mo devient ~150-300 Ko : upload traité vite, affichage
 * rapide sur tout le site.
 *
 * Silencieux et sans risque : si le fichier n'est pas une image gérée ou
 * que le traitement échoue, l'original est conservé tel quel.
 */
class ImageOptimizer
{
    public static function optimize(string $path, int $maxDim = 1600, int $quality = 80): void
    {
        try {
            $info = @getimagesize($path);

            if (! $info) {
                return;
            }

            [$width, $height, $type] = $info;

            $create = match ($type) {
                IMAGETYPE_JPEG => 'imagecreatefromjpeg',
                IMAGETYPE_PNG => 'imagecreatefrompng',
                IMAGETYPE_WEBP => 'imagecreatefromwebp',
                default => null, // gif (animations) et autres : intacts
            };

            if (! $create) {
                return;
            }

            $needsResize = max($width, $height) > $maxDim;

            // Petite image déjà légère : ré-encoder ne ferait que dégrader.
            if (! $needsResize && filesize($path) < 300 * 1024) {
                return;
            }

            $src = @$create($path);

            if (! $src) {
                return;
            }

            if ($needsResize) {
                $ratio = $maxDim / max($width, $height);
                $newW = (int) round($width * $ratio);
                $newH = (int) round($height * $ratio);

                $dst = imagecreatetruecolor($newW, $newH);

                // Préserve la transparence PNG/WebP
                if ($type !== IMAGETYPE_JPEG) {
                    imagealphablending($dst, false);
                    imagesavealpha($dst, true);
                }

                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);
                imagedestroy($src);
                $src = $dst;
            }

            match ($type) {
                IMAGETYPE_JPEG => imagejpeg($src, $path, $quality),
                IMAGETYPE_PNG => imagepng($src, $path, 8),
                IMAGETYPE_WEBP => imagewebp($src, $path, $quality),
            };

            imagedestroy($src);
        } catch (\Throwable) {
            // L'original reste en place.
        }
    }
}
