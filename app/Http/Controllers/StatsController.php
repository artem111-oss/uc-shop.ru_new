<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StatsController extends Controller
{
    public function getUcStats()
    {
        try {
            $now = Carbon::now();

            $baseQuery = Order::where(function ($q) {
                $q->where('payment_status', 'completed')
                  ->orWhere('status_id', 3);
            });

            $hourAgo  = $now->copy()->subHour();
            $weekAgo  = $now->copy()->subWeek();
            $monthAgo = $now->copy()->subMonth();

            $sqlHour = $this->calculateUcAmount(
                (clone $baseQuery)->where(function ($q) use ($hourAgo) {
                    $q->where('completed_at', '>=', $hourAgo)
                      ->orWhere(function ($q2) use ($hourAgo) {
                          $q2->whereNull('completed_at')->where('updated_at', '>=', $hourAgo);
                      });
                })->get()
            );

            $sqlWeek = $this->calculateUcAmount(
                (clone $baseQuery)->where(function ($q) use ($weekAgo) {
                    $q->where('completed_at', '>=', $weekAgo)
                      ->orWhere(function ($q2) use ($weekAgo) {
                          $q2->whereNull('completed_at')->where('updated_at', '>=', $weekAgo);
                      });
                })->get()
            );

            $sqlMonth = $this->calculateUcAmount(
                (clone $baseQuery)->where(function ($q) use ($monthAgo) {
                    $q->where('completed_at', '>=', $monthAgo)
                      ->orWhere(function ($q2) use ($monthAgo) {
                          $q2->whereNull('completed_at')->where('updated_at', '>=', $monthAgo);
                      });
                })->get()
            );

            $sqlTotal = $this->calculateUcAmount((clone $baseQuery)->get());

            $mongo = $this->fetchMongoStats();

            $ucLastHour  = $sqlHour  + ($mongo['hour']  ?? 0);
            $ucLastWeek  = $sqlWeek  + ($mongo['week']  ?? 0);
            $ucLastMonth = $sqlMonth + ($mongo['month'] ?? 0);
            $ucTotal     = $sqlTotal + ($mongo['total'] ?? 0);

            return response()->json([
                'success' => true,
                'stats' => [
                    'hour'  => $ucLastHour,
                    'week'  => $ucLastWeek,
                    'month' => $ucLastMonth,
                    'total' => $ucTotal,
                ],
                'formatted' => [
                    'hour'  => $this->formatNumber($ucLastHour),
                    'week'  => $this->formatNumber($ucLastWeek),
                    'month' => $this->formatNumber($ucLastMonth),
                    'total' => $this->formatNumber($ucTotal),
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('UC stats error', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Failed to fetch stats'], 500);
        }
    }

    private function fetchMongoStats(): array
    {
        $fallback = ['hour' => 0, 'week' => 0, 'month' => 0, 'total' => 0];

        try {
            $response = Http::timeout(3)->get('http://80.64.24.183:5055/stats');

            if ($response->successful()) {
                return $response->json('stats', $fallback);
            }

            Log::warning('UC Stats: mongo API bad response', ['status' => $response->status()]);
        } catch (\Throwable $e) {
            Log::warning('UC Stats: mongo API unavailable', ['message' => $e->getMessage()]);
        }

        return $fallback;
    }

    private function calculateUcAmount($orders): int
    {
        $totalUc = 0;

        foreach ($orders as $order) {
            if (empty($order->uc_amount)) {
                continue;
            }

            $parts = explode(',', $order->uc_amount);

            foreach ($parts as $part) {
                $part = trim($part);
                if (preg_match('/(\d+)\s*UC(?:\s*x(\d+))?/i', $part, $matches)) {
                    $ucAmount = (int) $matches[1];
                    $quantity = isset($matches[2]) ? (int) $matches[2] : 1;
                    $totalUc += $ucAmount * $quantity;
                }
            }
        }

        return $totalUc;
    }

    private function formatNumber(int $number): string
    {
        if ($number >= 1000000) {
            $v = $number / 1000000;
            return ($v == floor($v) ? (int)$v : number_format($v, 1, '.', '')) . 'M';
        } elseif ($number >= 1000) {
            $v = round($number / 1000, 1);
            return ($v == floor($v) ? (int)$v : number_format($v, 1, '.', '')) . 'K';
        }
        return (string) $number;
    }
}