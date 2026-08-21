<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DeliveryZoneController;
use App\Http\Controllers\Admin\NotificationLogController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\VerialSyncController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Email verification
    Route::get('/verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Confirm password
    Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store']);

    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // Dashboard — requires email verification
    Route::middleware('verified')->group(function () {
        Route::get('/dashboard', DashboardController::class)
            ->name('dashboard');
    });
});

/*
|--------------------------------------------------------------------------
| Public storefront
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');
Route::get('/catalogo', [StoreController::class, 'catalog'])->name('catalog');
Route::get('/catalogo/{product:slug}', [StoreController::class, 'product'])->name('product');
Route::get('/reparto', [StoreController::class, 'delivery'])->name('delivery');
Route::get('/contacto', [StoreController::class, 'contact'])->name('contact');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

/*
|--------------------------------------------------------------------------
| Carrito y checkout
|--------------------------------------------------------------------------
*/

Route::prefix('carrito')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/{product}', [CartController::class, 'add'])->name('add');
    Route::patch('/{product}', [CartController::class, 'update'])->name('update');
    Route::delete('/{product}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');
});

Route::get('/comprobar-codigo-postal', [CartController::class, 'checkPostalCode'])
    ->name('delivery.check');

Route::get('/finalizar-pedido', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/finalizar-pedido', [CheckoutController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('checkout.store');
Route::get('/pedido/{orderNumber}', [CheckoutController::class, 'confirmation'])
    ->name('checkout.confirmation');

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('products', ProductController::class)->except('show');
    Route::resource('orders', OrderController::class);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::get('orders/{order}/pdf', [OrderController::class, 'pdf'])->name('orders.pdf');
    Route::resource('delivery-zones', DeliveryZoneController::class)->except('show');
    Route::resource('posts', AdminPostController::class)->except('show');

    // Notifications
    Route::post('orders/{order}/notifications/send', [NotificationLogController::class, 'send'])->name('orders.notifications.send');
    Route::post('orders/{order}/notifications/{notification}/resend', [NotificationLogController::class, 'resend'])->name('orders.notifications.resend');
    Route::patch('orders/{order}/contact', [NotificationLogController::class, 'updateContact'])->name('orders.contact.update');

    // Verial
    Route::get('verial', [VerialSyncController::class, 'index'])->name('verial.index');
    Route::post('verial/sync-catalog', [VerialSyncController::class, 'syncCatalog'])->name('verial.sync-catalog');
    Route::post('verial/sync-catalog-incremental', [VerialSyncController::class, 'syncCatalogIncremental'])->name('verial.sync-catalog-incremental');
    Route::post('verial/sync-stock', [VerialSyncController::class, 'syncStock'])->name('verial.sync-stock');
    Route::post('verial/sync-images', [VerialSyncController::class, 'syncImages'])->name('verial.sync-images');
    Route::post('verial/send-orders', [VerialSyncController::class, 'sendPendingOrders'])->name('verial.send-orders');
    Route::post('verial/sync-order-status', [VerialSyncController::class, 'syncOrderStatus'])->name('verial.sync-order-status');
    Route::post('verial/upload-stock', [VerialSyncController::class, 'uploadStockCsv'])->name('verial.upload-stock');
    Route::post('verial/upload-prices', [VerialSyncController::class, 'uploadPricesCsv'])->name('verial.upload-prices');
});
