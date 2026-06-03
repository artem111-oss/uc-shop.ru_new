<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ===== Main Pages =====
Route::get('/', [ProductController::class, "index"])->name('home');

Route::get('/contacts', function () {
    return view('contacts');
})->name('contacts');

Route::get('/politic', function () {
    return view('politic');
})->name('politic');

Route::get('/refund', function () {
    return view('refund');
})->name('refund');

Route::get('/faq', function () {
    return view('faq');
})->name('faq');

Route::get('/agrement', function () {
    return view('agrement');
})->name('agrement');

Route::get('/terms', function () {
    return view('terms');
})->name('terms');

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/how-to-purchase', function () {
    return view('how-to-purchase');
})->name('how-to-purchase');

// ===== Contact Form =====
Route::get('/contacts/all', [ContactController::class, 'list'])
    ->name('contact-form');

Route::post('/contacts/submit', [ContactController::class, 'submit'])
    ->name('contact-submit')
    ->middleware('throttle:5,1'); // Rate limit: 5 запросов в минуту

// ===== Order Routes (Legacy) =====
Route::post("/order/add", [OrderController::class, "create"])
    ->name('order-create')
    ->middleware('throttle:100,1'); // 100 req/min для высокой нагрузки

Route::get("/order/{id}/{uid}", [OrderController::class, "orderUser"])
    ->name('order.view');

// ===== Payment Routes =====
Route::post('/order/payment', [OrderController::class, 'createPayment'])
    ->name('order.payment')
    ->middleware('throttle:100,1'); // 100 req/min для высокой нагрузки

Route::get('/payment/success', [OrderController::class, 'paymentSuccess'])
    ->name('payment.success');

Route::get('/payment/failed', [OrderController::class, 'paymentFail'])
    ->name('payment.failed');

// Legacy webhook route (используйте /api/payment/webhook)
// Pally webhook
Route::post('/payment/pallypostback', [OrderController::class, 'handlePallyCallback'])
    ->name('payment.pally.callback')
    ->withoutMiddleware(['web']); // Bypass CSRF for webhook

// Platima webhook
Route::post('/webhook/payment', [OrderController::class, 'handlePaymentCallback'])
    ->name('webhook.payment.callback')
    ->withoutMiddleware(['web']);

// ===== Testing Routes (only local) =====
Route::get("/tg", function (\App\Helpers\Telegram $telegram) {
    if (!\App::environment('local')) {
        return response('Not allowed', 403);
    }
    $http = $telegram->sendDocument(260901033, 'mstile-150x150.png', 15);
    return response()->json(['status' => 'ok', 'body' => $http->body()]);
});

Route::post('/test-tg', function (\Illuminate\Http\Request $request) {
    if (!\App::environment('local')) {
        return response('Not allowed', 403);
    }
    app(OrderController::class)->sendToTelegram("TEST: запрос дошёл!\nIP: " . $request->ip());
    \Log::info('test-tg hit');
    return response()->json(['ok' => true]);
});
