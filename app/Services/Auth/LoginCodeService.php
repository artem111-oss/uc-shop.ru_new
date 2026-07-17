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

    public function create(string $email): string
    {
        $code = (string) random_int(100000, 999999);

        DB::transaction(function () use ($email, $code): void {
            LoginCode::query()
                ->where('email', $email)
                ->whereNull('consumed_at')
                ->update(['consumed_at' => now()]);

            LoginCode::query()->create([
                'email' => $email,
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
            ]);
        });

        return $code;
    }

    public function verify(string $email, string $code): ?User
    {
        return DB::transaction(function () use ($email, $code): ?User {
            $loginCode = LoginCode::query()
                ->where('email', $email)
                ->whereNull('consumed_at')
                ->where('expires_at', '>=', now())
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$loginCode || $loginCode->attempts >= self::MAX_ATTEMPTS) {
                return null;
            }

            if (!Hash::check($code, $loginCode->code_hash)) {
                $loginCode->increment('attempts');

                return null;
            }

            $loginCode->update([
                'consumed_at' => now(),
            ]);

            return User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => Str::before($email, '@'),
                    'password' => Hash::make(Str::random(64)),
                ]
            );
        });
    }
}