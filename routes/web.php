<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Storefront (Spark Free Landing Page)
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [HomeController::class, 'shop'])->name('shop');
Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('product.show');

/*
|--------------------------------------------------------------------------
| Cart Routes (Customer Side — works for guest and logged-in customers)
|--------------------------------------------------------------------------
*/
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/', [CartController::class, 'store'])->name('store');
    Route::patch('/{cartItem}', [CartController::class, 'update'])->name('update');
    Route::delete('/{cartItem}', [CartController::class, 'destroy'])->name('destroy');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');
});

/*
|--------------------------------------------------------------------------
| Checkout Routes (Customer Side — works for guest and logged-in customers)
|--------------------------------------------------------------------------
*/
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/', [CheckoutController::class, 'store'])->name('store');
    Route::get('/success/{order}', [CheckoutController::class, 'success'])->name('success');
});

/*
|--------------------------------------------------------------------------
| Admin Auth Routes (Laravel Breeze)
|--------------------------------------------------------------------------
*/
Route::prefix('admin_dash')->group(function () {

    Route::middleware('guest')->group(function () {
        // Route::get('/register', [RegisteredUserController::class, 'create'])
        //     ->name('register');

        // Route::post('/register', [RegisteredUserController::class, 'store']);

        Route::get('/login', [AuthenticatedSessionController::class, 'create'])
            ->name('login');

        Route::post('/login', [AuthenticatedSessionController::class, 'store'])
            ->name('login.attempt');

        Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
            ->name('password.request');

        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
            ->name('password.email');

        Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
            ->name('password.reset');

        Route::post('/reset-password', [NewPasswordController::class, 'store'])
            ->name('password.store');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/verify-email', EmailVerificationPromptController::class)
            ->name('verification.notice');

        Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');

        Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('verification.send');

        Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])
            ->name('password.confirm');

        Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store']);

        Route::put('/password', [PasswordController::class, 'update'])
            ->name('password.update');

        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('logout');
    });

    /*
    |----------------------------------------------------------------------
    | Authenticated Admin Routes (Spark Admin Template)
    |----------------------------------------------------------------------
    */
    Route::middleware('auth')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Route::get('/tables/basic', function () {
        //     return view('tables.basic');
        // })->name('tables.basic');

        // Route::get('/ui/forms', function () {
        //     return view('ui.forms');
        // })->name('ui.forms');

        // Route::get('/ui/buttons', function () {
        //     return view('ui.buttons');
        // })->name('ui.buttons');

        // Route::get('/pages/blank', function () {
        //     return view('pages.blank');
        // })->name('pages.blank');

        // Route::get('/pages/404', function () {
        //     return view('errors.404');
        // })->name('pages.404');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        /*
        |------------------------------------------------------------------
        | Category Management Routes
        |------------------------------------------------------------------
        */
        Route::resource('categories', CategoryController::class)->except(['show']);

        /*
        |------------------------------------------------------------------
        | Product Management Routes
        |------------------------------------------------------------------
        */
        Route::resource('products', ProductController::class)->except(['show']);
        Route::patch('/products/{product}/add-stock', [ProductController::class, 'addStock'])->name('products.add-stock');
        Route::get('/products/{product}/stock-history', [ProductController::class, 'stockHistory'])->name('products.stock-history');

        /*
        |------------------------------------------------------------------
        | Order Management Routes
        |------------------------------------------------------------------
        */
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::patch('/orders/{order}/refund', [OrderController::class, 'refund'])->name('orders.refund');
    });
});
