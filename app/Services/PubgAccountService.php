<?php

namespace App\Services;

use App\Models\PubgAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PubgAccountService
{
    public function list(User $user)
    {
        return $user->pubgAccounts()
            ->orderByDesc('is_primary')
            ->orderByDesc('id')
            ->get();
    }

    public function create(User $user, array $data): PubgAccount
    {
        return DB::transaction(function () use ($user, $data): PubgAccount {
            $existing = $user->pubgAccounts()
                ->where('pubg_id', $data['pubg_id'])
                ->first();

            if ($existing) {
                return $existing;
            }

            $isFirst = $user->pubgAccounts()->count() === 0;
            $makePrimary = $isFirst || (bool) ($data['is_primary'] ?? false);

            if ($makePrimary) {
                $user->pubgAccounts()->update(['is_primary' => false]);
            }

            return $user->pubgAccounts()->create([
                'pubg_id' => $data['pubg_id'],
                'nickname' => $data['nickname'] ?? null,
                'is_primary' => $makePrimary,
            ]);
        });
    }

    public function update(User $user, PubgAccount $account, array $data): PubgAccount
    {
        $this->assertOwnership($user, $account);

        return DB::transaction(function () use ($user, $account, $data): PubgAccount {
            if (array_key_exists('nickname', $data)) {
                $account->nickname = $data['nickname'];
            }

            if (($data['is_primary'] ?? false) === true) {
                $user->pubgAccounts()->where('id', '!=', $account->id)->update(['is_primary' => false]);
                $account->is_primary = true;
            }

            $account->save();

            return $account->refresh();
        });
    }

    public function delete(User $user, PubgAccount $account): void
    {
        $this->assertOwnership($user, $account);

        DB::transaction(function () use ($user, $account): void {
            $wasPrimary = $account->is_primary;
            $account->delete();

            if ($wasPrimary) {
                $next = $user->pubgAccounts()->orderByDesc('id')->first();
                $next?->update(['is_primary' => true]);
            }
        });
    }

    private function assertOwnership(User $user, PubgAccount $account): void
    {
        if ($account->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'pubg_id' => ['Аккаунт не найден.'],
            ]);
        }
    }
}