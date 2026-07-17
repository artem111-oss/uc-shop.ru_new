<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AccountOrderController;
use App\Http\Controllers\Api\PubgAccountController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * Payment API Routes
 * 
 * POST /api/orders/create          - Создание заказа
 * POST /api/payment/init           - Инициализация платежа
 * POST /api/payment/webhook        - Вебхук от Platima
 */
Route::group(['prefix' => 'orders'], function () {
    Route::post('/create', [PaymentController::class, 'createOrder'])
        ->name('api.order.create')
        ->middleware('throttle:60,1'); // Rate limiting: 60 запросов в минуту (высокая нагрузка)
});

Route::group(['prefix' => 'payment'], function () {
    Route::post('/init', [PaymentController::class, 'initPayment'])
        ->name('api.payment.init')
        ->middleware('throttle:60,1'); // Rate limiting: 60 запросов в минуту (высокая нагрузка)
    
    Route::post('/webhook', [PaymentController::class, 'handleWebhook'])
        ->name('api.payment.webhook')
        ->withoutMiddleware(['api']); // Webhook не требует CSRF токена
});

/**
 * Stats API Routes
 * 
 * GET /api/stats/uc - Получить статистику отправленных UC
 */
Route::get('/stats/uc', [\App\Http\Controllers\StatsController::class, 'getUcStats'])
    ->name('api.stats.uc');

/**
 * Reviews API Routes
 * 
 * GET /api/reviews - Получить сгенерированные отзывы
 */
Route::get('/reviews', [\App\Http\Controllers\ReviewsController::class, 'getReviews'])
    ->name('api.reviews');

Route::middleware('throttle:5,1')
    ->prefix('auth')
    ->group(function (): void {
        Route::post('/request-code', [AuthController::class, 'requestCode'])
            ->name('api.auth.request-code');

        Route::post('/verify-code', [AuthController::class, 'verifyCode'])
            ->name('api.auth.verify-code');
    });

Route::middleware(['auth:sanctum', 'throttle:60,1'])
    ->prefix('auth')
    ->group(function (): void {
        Route::get('/me', [AuthController::class, 'me'])
            ->name('api.auth.me');

        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('api.auth.logout');
    });

Route::middleware(['auth:sanctum', 'throttle:60,1'])
    ->prefix('account')
    ->group(function (): void {
        Route::get('/orders', [AccountOrderController::class, 'index'])
            ->name('api.account.orders.index');

        Route::get('/orders/{orderId}', [AccountOrderController::class, 'show'])
            ->whereNumber('orderId')
            ->name('api.account.orders.show');

        Route::get('/pubg-accounts', [PubgAccountController::class, 'index'])
            ->name('api.account.pubg-accounts.index');

        Route::post('/pubg-accounts', [PubgAccountController::class, 'store'])
            ->name('api.account.pubg-accounts.store');

        Route::patch('/pubg-accounts/{id}', [PubgAccountController::class, 'update'])
            ->whereNumber('id')
            ->name('api.account.pubg-accounts.update');

        Route::delete('/pubg-accounts/{id}', [PubgAccountController::class, 'destroy'])
            ->whereNumber('id')
            ->name('api.account.pubg-accounts.destroy');
    });