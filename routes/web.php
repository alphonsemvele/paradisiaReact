<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Inertia\Inertia;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\InvestController;
use App\Http\Controllers\InvestPaymentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\VisitorController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PublicationController;
use App\Http\Controllers\Admin\PointDeVenteController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\PublicStatsController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\Admin\FormationController as AdminFormationController;
use App\Http\Controllers\Admin\InscriptionController as AdminInscriptionController;

// Route::view('/', 'welcome');

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

// Route::view('profile', 'profile')
//     ->middleware(['auth'])
//     ->name('profile');

// require __DIR__.'/auth.php';

Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->name('admin.')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        // Diagnostic d'envoi d'e-mail (réservé aux administrateurs)
        Route::match(['get', 'post'], '/diagnostic/mail', [\App\Http\Controllers\Admin\MailDiagnosticController::class, 'index'])
            ->name('diagnostic.mail');

        // À venir : visitors, users, products, etc.
           // 🆕 Visiteurs
        Route::get('/visitors', [VisitorController::class, 'index'])
            ->name('visitors');

            // 🆕 Utilisateurs
        Route::get('/users', ["App\Http\Controllers\Admin\UserController", 'index'])->name('users.index');
        Route::get('/users/{user}', ["App\Http\Controllers\Admin\UserController", 'show'])->name('users.show');
        Route::patch('/users/{user}', ["App\Http\Controllers\Admin\UserController", 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle-status', ["App\Http\Controllers\Admin\UserController", 'toggleStatus'])->name('users.toggle-status');
        Route::patch('/users/{user}/toggle-validation', ["App\Http\Controllers\Admin\UserController", 'toggleValidation'])->name('users.toggle-validation');
        Route::delete('/users/{user}', ["App\Http\Controllers\Admin\UserController", 'destroy'])->name('users.destroy');




        // 🆕 Catégories
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::patch('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::patch('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // 🆕 Produits
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        // POST + PATCH : l'upload multipart exige POST, mais le front spoofe _method=PATCH
        Route::match(['post', 'patch'], '/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');



// 🆕 Publications
Route::get('/publications', [PublicationController::class, 'index'])->name('publications.index');
Route::get('/publications/{publication}', [PublicationController::class, 'show'])->name('publications.show');
Route::patch('/publications/{publication}', [PublicationController::class, 'update'])->name('publications.update');
Route::patch('/publications/{publication}/status', [PublicationController::class, 'updateStatus'])->name('publications.update-status');
Route::delete('/publications/{publication}', [PublicationController::class, 'destroy'])->name('publications.destroy');
Route::delete('/publications/{publication}/comments/{comment}', [PublicationController::class, 'deleteComment'])->name('publications.delete-comment');



// 🆕 Points de vente
Route::get('/points-de-vente', [PointDeVenteController::class, 'index'])->name('points-de-vente.index');
Route::get('/points-de-vente/create', [PointDeVenteController::class, 'create'])->name('points-de-vente.create');
Route::post('/points-de-vente', [PointDeVenteController::class, 'store'])->name('points-de-vente.store');
Route::get('/points-de-vente/{point_de_vente}/edit', [PointDeVenteController::class, 'edit'])->name('points-de-vente.edit');
// POST + PATCH : l'upload multipart exige POST, mais le front spoofe _method=PATCH
Route::match(['post', 'patch'], '/points-de-vente/{point_de_vente}', [PointDeVenteController::class, 'update'])->name('points-de-vente.update');
Route::patch('/points-de-vente/{point_de_vente}/toggle-status', [PointDeVenteController::class, 'toggleStatus'])->name('points-de-vente.toggle-status');
Route::delete('/points-de-vente/{point_de_vente}', [PointDeVenteController::class, 'destroy'])->name('points-de-vente.destroy');



// 🆕 Formations (catalogue géré par l'admin)
Route::get('/formations', [AdminFormationController::class, 'index'])->name('formations.index');
Route::get('/formations/create', [AdminFormationController::class, 'create'])->name('formations.create');
Route::post('/formations', [AdminFormationController::class, 'store'])->name('formations.store');
Route::get('/formations/{formation}/edit', [AdminFormationController::class, 'edit'])->name('formations.edit');
Route::post('/formations/{formation}', [AdminFormationController::class, 'update'])->name('formations.update');
Route::patch('/formations/{formation}/toggle-status', [AdminFormationController::class, 'toggleStatus'])->name('formations.toggle-status');

        // ── Événements ────────────────────────────────────────────────────
        Route::get('/events', [\App\Http\Controllers\Admin\EventController::class, 'index'])->name('events.index');
        Route::get('/events/create', [\App\Http\Controllers\Admin\EventController::class, 'create'])->name('events.create');
        Route::post('/events', [\App\Http\Controllers\Admin\EventController::class, 'store'])->name('events.store');
        Route::get('/events/{event}/edit', [\App\Http\Controllers\Admin\EventController::class, 'edit'])->name('events.edit');
        Route::match(['post', 'patch'], '/events/{event}', [\App\Http\Controllers\Admin\EventController::class, 'update'])->name('events.update');
        Route::delete('/events/{event}', [\App\Http\Controllers\Admin\EventController::class, 'destroy'])->name('events.destroy');
        Route::get('/events/{event}/inscrits', [\App\Http\Controllers\Admin\EventController::class, 'registrations'])->name('events.registrations');
        Route::post('/events/{event}/envoyer-lien', [\App\Http\Controllers\Admin\EventController::class, 'envoyerLien'])->name('events.envoyer-lien');
Route::delete('/formations/{formation}', [AdminFormationController::class, 'destroy'])->name('formations.destroy');

// 🆕 Inscriptions aux formations
Route::get('/inscriptions', [AdminInscriptionController::class, 'index'])->name('inscriptions.index');
Route::patch('/inscriptions/{inscription}/status', [AdminInscriptionController::class, 'updateStatus'])->name('inscriptions.update-status');
Route::delete('/inscriptions/{inscription}', [AdminInscriptionController::class, 'destroy'])->name('inscriptions.destroy');


// 🆕 Ventes
Route::get('/sales', [SaleController::class, 'dashboard'])->name('sales.dashboard');
Route::get('/sales/list', [SaleController::class, 'index'])->name('sales.index');
Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
Route::patch('/sales/{sale}/status', [SaleController::class, 'updateStatus'])->name('sales.update-status');
Route::delete('/sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');
    });

Route::get('/', function () {
    return Inertia::render('welcome');
});

Route::get('/', [HomeController::class, 'index'])->name('accueil');

Route::post('/logout', [UserController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    // Register
    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

// Logout (utilisateurs connectés)
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');



Route::get('/points-de-vente', [PublicStatsController::class, 'pointsDeVente'])->name('points-de-vente.public.index');
Route::get('/points-de-vente/{pointDeVente}', [PublicStatsController::class, 'pointDeVenteShow'])->name('points-de-vente.public.show');
Route::get('/statistiques', [PublicStatsController::class, 'statistiques'])->name('statistiques.public');

// Formations en présentiel (liste publique + inscription)
Route::get('/formations', [FormationController::class, 'index'])->name('formations.index');
Route::get('/formations/{formation}', [FormationController::class, 'show'])->name('formations.show');

// ── Événements (public) ───────────────────────────────────────────────────────
Route::get('/events', [\App\Http\Controllers\EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [\App\Http\Controllers\EventController::class, 'show'])->name('events.show');
Route::post('/events/{event}/inscription', [\App\Http\Controllers\EventController::class, 'register'])->name('events.register');
Route::post('/formations/{formation}/inscription', [FormationController::class, 'register'])->name('formations.register');
Route::get('/products/{product}/stats', [PublicStatsController::class, 'productStats'])->name('products.public.stats');

// Lien court de partage d'une publication : URL propre, aperçu Open Graph
// (image + texte) rendu côté serveur pour WhatsApp, Facebook, Telegram…
Route::get('/p/{publication}', [HomeController::class, 'index'])->name('publication.share');

// Boutique et panier : accessibles aux visiteurs (panier en session) ;
// la connexion n'est exigée qu'au moment de passer commande (/checkout).
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/shop/products/{id}', [ShopController::class, 'show'])->name('shop.product');
Route::post('/cart/add', [ShopController::class, 'addToCart'])->name('cart.add');
Route::patch('/cart/update', [ShopController::class, 'updateCart'])->name('cart.update');
Route::delete('/cart/remove', [ShopController::class, 'removeFromCart'])->name('cart.remove');
Route::delete('/cart/clear', [ShopController::class, 'clearCart'])->name('cart.clear');



Route::middleware('auth')->group(function () {
    // Publications
    Route::post('/publications', [HomeController::class, 'publishPost'])->name('publication.store');
    Route::patch('/publications/{id}', [HomeController::class, 'updatePost'])->name('publication.update'); // 🆕
    Route::delete('/publications/{id}', [HomeController::class, 'deletePost'])->name('publication.delete'); // 🆕
    Route::post('/publications/{id}/like', [HomeController::class, 'toggleLike'])->name('publication.like');
    Route::post('/publications/{id}/comment', [HomeController::class, 'addComment'])->name('publication.comment');
    Route::post('/publications/{id}/share', [HomeController::class, 'recordShare'])->name('publication.share');
    // Statistiques d'une publication (réservées à son auteur)
    Route::get('/publications/{id}/stats', [HomeController::class, 'publicationStats'])->name('publication.stats');
    
    // Comments
    Route::patch('/comments/{id}', [HomeController::class, 'updateComment'])->name('comment.update');
    Route::delete('/comments/{id}', [HomeController::class, 'deleteComment'])->name('comment.delete');
    Route::post('/comments/{id}/like', [HomeController::class, 'toggleCommentLike'])->name('comment.like'); // 🆕

    //investir

    Route::get('/invest', [InvestController::class, 'index'])->name('invest');

    // Paiement d'un investissement par portefeuille Malapay
    Route::get('/invest/paiement/pays', [InvestPaymentController::class, 'pays'])->name('invest.pays');
    Route::post('/invest/paiement/verifier', [InvestPaymentController::class, 'verifier'])->name('invest.verifier');
    Route::post('/invest/paiement', [InvestPaymentController::class, 'payer'])->name('invest.payer');
    // Suivi pendant que le titulaire valide depuis sa boîte e-mail
    Route::get('/invest/paiement/statut/{reference}', [InvestPaymentController::class, 'statut'])->name('invest.statut');


    // Commande (nécessite un compte : la connexion renvoie ensuite au checkout)
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [OrderController::class, 'store'])->name('checkout.store');
    Route::get('/orders/{ref}', [OrderController::class, 'confirmation'])->name('orders.confirmation');


Route::post('/invest/quick', [InvestController::class, 'quickInvest'])->name('invest.quick');

});
















