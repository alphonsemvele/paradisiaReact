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

    /**
     * Produit une vignette de partage 1200×630 (ratio 1.91:1, exactement ce
     * qu'attendent WhatsApp et Facebook), recadrée au centre et compressée
     * sous ~200 Ko. Générée une fois puis mise en cache sur disque.
     *
     * @param  string  $sourceAbsolu  chemin absolu de l'image source
     * @param  string  $destinationRelative  chemin relatif sous public/ où écrire
     * @return bool  succès de la génération
     */
    public static function vignettePartage(string $sourceAbsolu, string $destinationAbsolue): bool
    {
        try {
            $info = @getimagesize($sourceAbsolu);

            if (! $info) {
                return false;
            }

            [$width, $height, $type] = $info;

            $create = match ($type) {
                IMAGETYPE_JPEG => 'imagecreatefromjpeg',
                IMAGETYPE_PNG => 'imagecreatefrompng',
                IMAGETYPE_WEBP => 'imagecreatefromwebp',
                default => null,
            };

            if (! $create) {
                return false;
            }

            $src = @$create($sourceAbsolu);

            if (! $src) {
                return false;
            }

            $cibleW = 1200;
            $cibleH = 630;

            // Recadrage « cover » : on remplit tout le cadre sans déformer,
            // en rognant le débordement, centré.
            $ratioSrc = $width / $height;
            $ratioCible = $cibleW / $cibleH;

            if ($ratioSrc > $ratioCible) {
                $copieH = $height;
                $copieW = (int) round($height * $ratioCible);
                $srcX = (int) round(($width - $copieW) / 2);
                $srcY = 0;
            } else {
                $copieW = $width;
                $copieH = (int) round($width / $ratioCible);
                $srcX = 0;
                $srcY = (int) round(($height - $copieH) / 2);
            }

            $dst = imagecreatetruecolor($cibleW, $cibleH);
            imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $cibleW, $cibleH, $copieW, $copieH);

            $dossier = dirname($destinationAbsolue);
            if (! is_dir($dossier)) {
                @mkdir($dossier, 0755, true);
            }

            // JPEG qualité 82 : ~150-200 Ko pour du 1200×630, bien sous la
            // limite au-delà de laquelle WhatsApp cesse d'afficher l'image.
            imagejpeg($dst, $destinationAbsolue, 82);

            imagedestroy($src);
            imagedestroy($dst);

            return is_file($destinationAbsolue);
        } catch (\Throwable) {
            return false;
        }
    }
}
