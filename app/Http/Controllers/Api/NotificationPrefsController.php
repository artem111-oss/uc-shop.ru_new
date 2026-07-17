<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NotificationPrefsController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'notify_email' => ['sometimes', 'boolean'],
            'notify_telegram' => ['sometimes', 'boolean'],
        ]);

        $user = $request->user();

        if (array_key_exists('notify_telegram', $validated) && $validated['notify_telegram'] && !$user->telegramLinks()->exists()) {
            return response()->json([
                'message' => 'Сначала привяжите Telegram.',
            ], 422);
        }

        $user->fill($validated);
        $user->save();

        return response()->json([
            'data' => [
                'notify_email' => (bool) $user->notify_email,
                'notify_telegram' => (bool) $user->notify_telegram,
            ],
        ]);
    }
}