<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Inertia\Inertia;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\InvestController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;


// Route::view('/', 'welcome');

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

// Route::view('profile', 'profile')
//     ->middleware(['auth'])
//     ->name('profile');

// require __DIR__.'/auth.php';



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

Route::middleware('auth')->group(function () {
    // Publications
    Route::post('/publications', [HomeController::class, 'publishPost'])->name('publication.store');
    Route::patch('/publications/{id}', [HomeController::class, 'updatePost'])->name('publication.update'); // 🆕
    Route::delete('/publications/{id}', [HomeController::class, 'deletePost'])->name('publication.delete'); // 🆕
    Route::post('/publications/{id}/like', [HomeController::class, 'toggleLike'])->name('publication.like');
    Route::post('/publications/{id}/comment', [HomeController::class, 'addComment'])->name('publication.comment');
    Route::post('/publications/{id}/share', [HomeController::class, 'recordShare'])->name('publication.share');
    
    // Comments
    Route::patch('/comments/{id}', [HomeController::class, 'updateComment'])->name('comment.update');
    Route::delete('/comments/{id}', [HomeController::class, 'deleteComment'])->name('comment.delete');
    Route::post('/comments/{id}/like', [HomeController::class, 'toggleCommentLike'])->name('comment.like'); // 🆕

    //investir

    Route::get('/invest', [InvestController::class, 'index'])->name('invest');


    // Boutique
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/shop/products/{id}', [ShopController::class, 'show'])->name('shop.product');

// Panier
Route::post('/cart/add', [ShopController::class, 'addToCart'])->name('cart.add');
Route::patch('/cart/update', [ShopController::class, 'updateCart'])->name('cart.update');
Route::delete('/cart/remove', [ShopController::class, 'removeFromCart'])->name('cart.remove');
Route::delete('/cart/clear', [ShopController::class, 'clearCart'])->name('cart.clear');

  Route::post('/checkout', [ShopController::class, 'checkout'])->name('checkout');


Route::post('/invest/quick', [InvestController::class, 'quickInvest'])->name('invest.quick');

});
















