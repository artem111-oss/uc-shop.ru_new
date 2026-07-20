<?php

namespace App\Services\Auth;

use App\Models\LoginCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoginCodeService
{
    private const CODE_TTL_MINUTES = 10;

    private const MAX_ATTEMPTS = 5;

    public function create(string $email, string $purpose = 'login'): string
    {
        $code = (string) random_int(100000, 999999);

        DB::transaction(function () use ($email, $code, $purpose): void {
            LoginCode::query()
                ->where('email', $email)
                ->where('purpose', $purpose)
                ->whereNull('consumed_at')
                ->update(['consumed_at' => now()]);

            LoginCode::query()->create([
                'email' => $email,
                'purpose' => $purpose,
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
            ]);
        });

        return $code;
    }

    public function verify(string $email, string $code, string $purpose = 'login'): ?User
    {
        return DB::transaction(function () use ($email, $code, $purpose): ?User {
            if (!$this->consume($email, $code, $purpose)) {
                return null;
            }

            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => Str::before($email, '@'),
                    'password' => Hash::make(Str::random(64)),
                ]
            );

            if (!$user->email_verified_at) {
                $user->forceFill([
                    'email_verified_at' => now(),
                ])->save();
            }

            return $user;
        });
    }

    public function consume(string $email, string $code, string $purpose): bool
    {
        return DB::transaction(function () use ($email, $code, $purpose): bool {
            $loginCode = LoginCode::query()
                ->where('email', $email)
                ->where('purpose', $purpose)
                ->whereNull('consumed_at')
                ->where('expires_at', '>=', now())
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$loginCode || $loginCode->attempts >= self::MAX_ATTEMPTS) {
                return false;
            }

            if (!Hash::check($code, $loginCode->code_hash)) {
                $loginCode->increment('attempts');

                return false;
            }

            $loginCode->update([
                'consumed_at' => now(),
            ]);

            return true;
        });
    }
}