<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RequestLoginCodeRequest;
use App\Http\Requests\Auth\VerifyLoginCodeRequest;
use App\Mail\LoginCodeMail;
use App\Services\Auth\LoginCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    private const EMAIL_RATE_LIMIT = 3;

    private const EMAIL_RATE_LIMIT_SECONDS = 600;

    public function __construct(
        private readonly LoginCodeService $loginCodeService
    ) {
    }

    public function requestCode(RequestLoginCodeRequest $request): JsonResponse
    {
        $email = mb_strtolower($request->validated('email'));
        $rateLimitKey = 'customer-login-code:' . hash('sha256', $email);

        if (RateLimiter::tooManyAttempts($rateLimitKey, self::EMAIL_RATE_LIMIT)) {
            return $this->genericRequestCodeResponse();
        }

        RateLimiter::hit($rateLimitKey, self::EMAIL_RATE_LIMIT_SECONDS);

        $code = $this->loginCodeService->create($email);

        try {
            Mail::to($email)->send(new LoginCodeMail($code));
        } catch (\Throwable $exception) {
            Log::warning('Customer login code email delivery failed.', [
                'exception_class' => $exception::class,
                'error' => $exception->getMessage(),
            ]);
        }

        return $this->genericRequestCodeResponse();
    }

    public function verifyCode(VerifyLoginCodeRequest $request): JsonResponse
    {
        $email = mb_strtolower($request->validated('email'));

        $user = $this->loginCodeService->verify(
            $email,
            $request->validated('code')
        );

        if (!$user) {
            return response()->json([
                'message' => 'Код неверен, истёк или больше недоступен.',
            ], 422);
        }

        $deviceName = preg_replace(
            '/[^a-zA-Z0-9._-]/',
            '',
            (string) $request->input('device_name', 'web')
        );

        $token = $user->createToken(
            'customer:' . substr($deviceName ?: 'web', 0, 90)
        )->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_is_linked' => !preg_match('/^telegram-\d+@users\.uc-shop\.ru$/', $user->email),
                'notify_email' => (bool) $user->notify_email,
                'notify_telegram' => (bool) $user->notify_telegram,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Вы вышли из личного кабинета.',
        ]);
    }

    private function genericRequestCodeResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'Если адрес введён верно, код отправлен на email.',
        ]);
    }
}