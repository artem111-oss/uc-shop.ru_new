<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PubgAccount\StorePubgAccountRequest;
use App\Http\Requests\PubgAccount\UpdatePubgAccountRequest;
use App\Models\PubgAccount;
use App\Services\PubgAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PubgAccountController extends Controller
{
    public function __construct(
        private readonly PubgAccountService $service
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $accounts = $this->service->list($request->user());

        return response()->json([
            'data' => $accounts->map(fn (PubgAccount $a): array => $this->toArray($a))->values(),
        ]);
    }

    public function store(StorePubgAccountRequest $request): JsonResponse
    {
        $account = $this->service->create($request->user(), $request->validated());

        return response()->json([
            'data' => $this->toArray($account),
        ], 201);
    }

    public function update(UpdatePubgAccountRequest $request, int $id): JsonResponse
    {
        $account = PubgAccount::query()
            ->where('user_id', $request->user()->id)
            ->find($id);

        if (!$account) {
            return response()->json(['message' => 'Аккаунт не найден.'], 404);
        }

        $account = $this->service->update($request->user(), $account, $request->validated());

        return response()->json([
            'data' => $this->toArray($account),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $account = PubgAccount::query()
            ->where('user_id', $request->user()->id)
            ->find($id);

        if (!$account) {
            return response()->json(['message' => 'Аккаунт не найден.'], 404);
        }

        $this->service->delete($request->user(), $account);

        return response()->json(['message' => 'Аккаунт удалён.']);
    }

    private function toArray(PubgAccount $account): array
    {
        return [
            'id' => $account->id,
            'pubg_id' => $account->pubg_id,
            'nickname' => $account->nickname,
            'is_primary' => $account->is_primary,
            'created_at' => $account->created_at?->toIso8601String(),
        ];
    }
}