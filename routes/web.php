<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\WishlistController as AdminWishlistController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WishlistController;
use App\Models\Media;
use Illuminate\Support\Facades\Route;

// Images stored in the database (uploaded via the MCP set_product_image tool).
Route::get('/media/{media}', function (Media $media) {
    return response(base64_decode($media->data), 200, [
        'Content-Type' => $media->mime,
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
})->whereNumber('media')->name('media.show');

// Public shop pages.
Route::get('/', [ShopController::class, 'home'])->name('home');
Route::get('/products', [ShopController::class, 'products'])->name('products');
Route::get('/product/{product}', [ShopController::class, 'show'])->name('product.show');
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
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
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

/*
|--------------------------------------------------------------------------
| Admin panel
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    // Auth (accessible without the admin middleware).
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::get('/auth/google/redirect', [AdminAuthController::class, 'redirectToGoogle'])->name('google.redirect');
    Route::get('/auth/google/callback', [AdminAuthController::class, 'handleGoogleCallback'])->name('google.callback');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    // Protected admin area.
    Route::middleware('admin')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('products', ProductController::class)->except('show');
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('reviews', ReviewController::class)->except('show');
        Route::get('/wishlists', [AdminWishlistController::class, 'index'])->name('wishlists.index');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::patch('/customers/{customer}/block', [CustomerController::class, 'toggleBlock'])->name('customers.block');

        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

        Route::get('/messages', [ContactMessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/{message}', [ContactMessageController::class, 'show'])->name('messages.show');
        Route::delete('/messages/{message}', [ContactMessageController::class, 'destroy'])->name('messages.destroy');

        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
