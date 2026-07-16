<?php

namespace App\Http\Controllers;

use App\Services\ImageOptimizer;
use Illuminate\Support\Facades\Storage;

abstract class Controller
{
    /**
     * URL d'un média. Priorité aux fichiers servis depuis public/ (docroot en
     * production, voir bootstrap/app.php) ; repli sur le disque "public"
     * (storage/) pour les anciens fichiers. Si le fichier n'existe nulle part
     * (données héritées orphelines), on renvoie null plutôt qu'un lien cassé.
     */
    protected function mediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (file_exists(public_path($path))) {
            return asset($path);
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::url($path);
        }

        return null;
    }

    /**
     * Enregistre un fichier directement dans public/ (docroot en production),
     * afin qu'il soit servi sans dépendre du lien symbolique storage:link.
     * Retourne le chemin relatif à stocker en base.
     */
    protected function uploadPublicFile($file, string $folder, string $prefix = 'pub'): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = $prefix.'_'.uniqid().'_'.time().'.'.$extension;
        $destination = public_path($folder);

        if (! file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        $file->move($destination, $filename);

        // Les images sont redimensionnées/compressées automatiquement :
        // une photo de téléphone de 8 Mo devient quelques centaines de Ko.
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            ImageOptimizer::optimize($destination.'/'.$filename);
        }

        return $folder.'/'.$filename;
    }

    /**
     * Supprime un média, qu'il vive dans public/ (nouveau système) ou sur le
     * disque "public" storage/ (anciens fichiers).
     */
    protected function deletePublicFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        // Certaines anciennes valeurs en base sont des URL complètes
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $path = ltrim((string) parse_url($path, PHP_URL_PATH), '/');
        }

        if (file_exists(public_path($path))) {
            @unlink(public_path($path));
        } elseif (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
