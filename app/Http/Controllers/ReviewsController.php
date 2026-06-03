<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ReviewsController extends Controller
{
    /**
     * Получить сгенерированные отзывы
     */
    public function getReviews()
    {
        $reviews = Cache::get('generated_reviews', []);
        
        // Обновляем time_ago для каждого отзыва
        $reviews = array_map(function($review) {
            $minutesAgo = Carbon::parse($review['created_at'])->diffInMinutes(now());
            $review['time_ago'] = $this->getTimeAgo($minutesAgo);
            return $review;
        }, $reviews);

        // Сортируем по времени создания (новые первые)
        usort($reviews, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return response()->json([
            'success' => true,
            'reviews' => array_values($reviews),
            'count' => count($reviews)
        ]);
    }

    private function getTimeAgo($minutes)
    {
        if ($minutes < 1) {
            return 'только что';
        } elseif ($minutes < 60) {
            return $minutes . ' мин назад';
        } elseif ($minutes < 120) {
            return '1 час назад';
        } elseif ($minutes < 180) {
            return '2 часа назад';
        } elseif ($minutes < 240) {
            return '3 часа назад';
        } elseif ($minutes < 300) {
            return '4 часа назад';
        } else {
            $hours = floor($minutes / 60);
            return $hours . ' часов назад';
        }
    }
}
