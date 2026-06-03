<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatsController extends Controller
{
    /**
     * Получить статистику отправленных UC
     */
    public function getUcStats()
    {
        try {
            // Текущее время
            $now = Carbon::now();
            
            // За последний час (используем completed_at для корректной фильтрации)
            $hourAgo = $now->copy()->subHour();
            $ucLastHour = $this->calculateUcAmount(
                Order::where('payment_status', 'completed')
                    ->where(function($q) use ($hourAgo) {
                        $q->where('completed_at', '>=', $hourAgo)
                          ->orWhere(function($q2) use ($hourAgo) {
                              $q2->whereNull('completed_at')
                                 ->where('updated_at', '>=', $hourAgo);
                          });
                    })
                    ->get()
            );
            
            // За последнюю неделю
            $weekAgo = $now->copy()->subWeek();
            $ucLastWeek = $this->calculateUcAmount(
                Order::where('payment_status', 'completed')
                    ->where(function($q) use ($weekAgo) {
                        $q->where('completed_at', '>=', $weekAgo)
                          ->orWhere(function($q2) use ($weekAgo) {
                              $q2->whereNull('completed_at')
                                 ->where('updated_at', '>=', $weekAgo);
                          });
                    })
                    ->get()
            );
            
            // За последний месяц
            $monthAgo = $now->copy()->subMonth();
            $ucLastMonth = $this->calculateUcAmount(
                Order::where('payment_status', 'completed')
                    ->where(function($q) use ($monthAgo) {
                        $q->where('completed_at', '>=', $monthAgo)
                          ->orWhere(function($q2) use ($monthAgo) {
                              $q2->whereNull('completed_at')
                                 ->where('updated_at', '>=', $monthAgo);
                          });
                    })
                    ->get()
            );
            
            // Всего за все время
            $ucTotal = $this->calculateUcAmount(
                Order::where('payment_status', 'completed')->get()
            );
            
            return response()->json([
                'success' => true,
                'stats' => [
                    'hour' => $ucLastHour,
                    'week' => $ucLastWeek,
                    'month' => $ucLastMonth,
                    'total' => $ucTotal,
                ],
                'formatted' => [
                    'hour' => $this->formatNumber($ucLastHour),
                    'week' => $this->formatNumber($ucLastWeek),
                    'month' => $this->formatNumber($ucLastMonth),
                    'total' => $this->formatNumber($ucTotal),
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch stats',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Подсчитать общее количество UC из заказов
     */
    private function calculateUcAmount($orders)
    {
        $totalUc = 0;
        
        foreach ($orders as $order) {
            if (empty($order->uc_amount)) {
                continue;
            }
            
            // Парсим строку uc_amount
            // Форматы: "60 UC", "60 UC x2", "60 UC, 180 UC", "60 UC x2, 180 UC x3"
            $parts = explode(',', $order->uc_amount);
            
            foreach ($parts as $part) {
                $part = trim($part);
                
                // Парсим "60 UC" или "60 UC x2"
                if (preg_match('/(\d+)\s*UC(?:\s*x(\d+))?/i', $part, $matches)) {
                    $ucAmount = (int) $matches[1];
                    $quantity = isset($matches[2]) ? (int) $matches[2] : 1;
                    
                    $totalUc += $ucAmount * $quantity;
                }
            }
        }
        
        return $totalUc;
    }
    
    /**
     * Форматировать число с разделителями тысяч
     * Округление применяется только после 10000 UC
     */
    private function formatNumber($number)
    {
        if ($number >= 1000000) {
            // Для миллионов: 1M, 1.2M и т.д.
            $millions = $number / 1000000;
            if ($millions == floor($millions)) {
                return ((int) $millions) . 'M';
            }
            return number_format($millions, 1, '.', '') . 'M';
        } elseif ($number >= 10000) {
            // После 10000: 10K, 10.1K, 15.2K и т.д.
            $thousands = $number / 1000;
            $formatted = round($thousands, 1);
            if ($formatted == floor($formatted)) {
                return ((int) $formatted) . 'K';
            }
            return number_format($formatted, 1, '.', '') . 'K';
        } elseif ($number >= 1000) {
            // От 1000 до 9999: 1K, 8.9K и т.д.
            $thousands = $number / 1000;
            if ($thousands == floor($thousands)) {
                return ((int) $thousands) . 'K';
            }
            return number_format($thousands, 1, '.', '') . 'K';
        }
        
        // До 1000: показываем точное число без округления
        return (string) $number;
    }
}
