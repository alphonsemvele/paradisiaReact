<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //

         $middleware->web(prepend: [
        \App\Http\Middleware\BlockBannedIp::class,
    ]);
         $middleware->web(append: [
        \App\Http\Middleware\TrackVisit::class,
    ]);
 $middleware->alias([
        'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
    ]);
         $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
        \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// Hébergement mutualisé (N0C) : le docroot est ~/public_html, un vrai dossier
// voisin du projet, pas public/. On y pointe public_path() pour que les
// uploads (produits, publications...) soient écrits directement là où le
// serveur web les sert. En local, aucun dossier ../public_html n'existe :
// public_path() reste public/ du projet.
$docroot = dirname($app->basePath()).'/public_html';

if (is_dir($docroot) && is_file($docroot.'/index.php')) {
    $app->usePublicPath($docroot);
}

return $app;
