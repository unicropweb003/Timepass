<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

// Public shop pages.
Route::get('/', [ShopController::class, 'home'])->name('home');
Route::get('/products', [ShopController::class, 'products'])->name('products');
Route::get('/search', [ShopController::class, 'search'])->name('search');
Route::get('/about', [ShopController::class, 'about'])->name('about');
Route::get('/contact', [ShopController::class, 'contact'])->name('contact');
Route::post('/contact', [ShopController::class, 'contactSubmit'])->name('contact.submit');

// Guest pages.
Route::middleware('guest')->group(function () {
    Route::view('/register', 'auth.register')->name('register');
    Route::view('/login', 'auth.login')->name('login');
});

// JSON API endpoints (consumed by the pages via fetch).
// They live under the web middleware so sessions + CSRF work in the browser.
Route::prefix('api')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('guest')->name('api.register');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('guest')->name('api.login');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')->name('logout');

// My Account (logged-in customers only).
Route::middleware('auth')->prefix('account')->name('account.')->controller(AccountController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/orders', 'orders')->name('orders');
    Route::get('/orders/{orderNumber}', 'showOrder')->name('orders.show');
    Route::get('/transactions', 'transactions')->name('transactions');
    Route::get('/addresses', 'addresses')->name('addresses');
    Route::post('/addresses', 'storeAddress')->name('addresses.store');
    Route::patch('/addresses/{address}/default', 'defaultAddress')->name('addresses.default');
    Route::delete('/addresses/{address}', 'destroyAddress')->name('addresses.destroy');
    Route::get('/profile', 'profile')->name('profile');
    Route::put('/profile', 'updateProfile')->name('profile.update');
    Route::put('/password', 'updatePassword')->name('password.update');
});
