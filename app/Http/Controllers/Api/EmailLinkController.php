<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\LoginCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use App\Mail\LoginCodeMail;

class EmailLinkController extends Controller
{
    private const RATE_LIMIT = 3;

    private const RATE_LIMIT_SECONDS = 600;

    public function __construct(
        private readonly LoginCodeService $loginCodeService
    ) {
    }

    public function requestCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
        ]);

        $email = mb_strtolower($validated['email']);
        $user = $request->user();

        if ($this->isRealEmail($user->email)) {
            return response()->json([
                'message' => 'Email уже привязан к этому аккаунту.',
            ], 422);
        }

        $emailOwner = User::query()
            ->where('email', $email)
            ->whereKeyNot($user->id)
            ->exists();

        if ($emailOwner) {
            return response()->json([
                'message' => 'Этот email уже используется другим аккаунтом. Войдите через email.',
            ], 409);
        }

        $rateLimitKey = 'customer-email-link:' . $user->id;

        if (RateLimiter::tooManyAttempts($rateLimitKey, self::RATE_LIMIT)) {
            return $this->genericResponse();
        }

        RateLimiter::hit($rateLimitKey, self::RATE_LIMIT_SECONDS);

        $code = $this->loginCodeService->create($email, 'email_link');

        try {
            Mail::to($email)->send(new LoginCodeMail($code));
        } catch (\Throwable $exception) {
            Log::warning('Customer email link code delivery failed.', [
                'exception_class' => $exception::class,
                'error' => $exception->getMessage(),
            ]);
        }

        return $this->genericResponse();
    }

    public function verifyCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'code' => ['required', 'digits:6'],
        ]);

        $email = mb_strtolower($validated['email']);
        $user = $request->user();

        if ($this->isRealEmail($user->email)) {
            return response()->json([
                'message' => 'Email уже привязан к этому аккаунту.',
            ], 422);
        }

        $emailOwner = User::query()
            ->where('email', $email)
            ->whereKeyNot($user->id)
            ->exists();

        if ($emailOwner) {
            return response()->json([
                'message' => 'Этот email уже используется другим аккаунтом. Войдите через email.',
            ], 409);
        }

        if (!$this->loginCodeService->consume($email, $validated['code'], 'email_link')) {
            throw ValidationException::withMessages([
                'code' => ['Код неверен, истёк или больше недоступен.'],
            ]);
        }

        try {
            $user->forceFill([
                'email' => $email,
                'email_verified_at' => now(),
                'notify_email' => true,
            ])->save();
        } catch (\Illuminate\Database\QueryException) {
            return response()->json([
                'message' => 'Этот email уже используется другим аккаунтом. Войдите через email.',
            ], 409);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_is_linked' => true,
                'notify_email' => (bool) $user->notify_email,
                'notify_telegram' => (bool) $user->notify_telegram,
            ],
        ]);
    }

    private function genericResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'Если адрес доступен для привязки, код отправлен на email.',
        ]);
    }

    private function isRealEmail(string $email): bool
    {
        return !preg_match('/^telegram-\d+@users\.uc-shop\.ru$/', $email);
    }
}