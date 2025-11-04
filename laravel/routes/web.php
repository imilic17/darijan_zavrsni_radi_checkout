<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProizvodController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\CountryTownController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| All user-facing routes for TechShop
|--------------------------------------------------------------------------
*/

// ---------------------
// Homepage (index.blade.php)
// ---------------------
Route::get('/', [ProizvodController::class, 'home'])->name('index.index');

// ---------------------
// Products / Categories (category.blade.php)
// ---------------------
Route::get('/proizvodi', [ProizvodController::class, 'list'])->name('proizvodi.index');
Route::get('/kategorija/{id}', [ProizvodController::class, 'kategorija'])->name('proizvodi.kategorija');

// AJAX search (used by category.blade.php JS)
Route::get('/ajax/proizvodi', [ProizvodController::class, 'ajaxSearch'])->name('proizvodi.search');
Route::get('/countries/search', [CountryController::class, 'search'])->name('countries.search');

// ---------------------
// Cart (kosarica)
// ---------------------
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');
Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');

// ---------------------
// Single product page
// ---------------------
Route::get('/proizvod/{id}', [ProizvodController::class, 'show'])->name('proizvod.show');

// ---------------------
// Dashboard (for logged-in users only)
// ---------------------
Route::get('/dashboard', function () {
    return view('dashboard'); // points to resources/views/dashboard.blade.php
})->middleware(['auth', 'verified'])->name('dashboard');

// ---------------------
// Authenticated user routes
// ---------------------
Route::middleware(['auth'])->group(function () {

    // ---------- PROFILE ----------
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Manage addresses
    Route::post('/profile/address/{id}/default', [ProfileController::class, 'setDefaultAddress'])
        ->name('profile.address.default');

        Route::post('/profile/address/add', [ProfileController::class, 'addAddress'])
    ->name('profile.address.add');

Route::post('/profile/address/delete', [ProfileController::class, 'deleteAddress'])
    ->name('profile.address.delete');
    // ---------- CHECKOUT ----------
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    // ---------- ORDERS ----------
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
});

Route::get('/towns/search', [CountryTownController::class, 'search'])->name('towns.search');

// ---------------------
// Auth routes (Laravel Breeze / Jetstream / Fortify)
// ---------------------
require __DIR__.'/auth.php';