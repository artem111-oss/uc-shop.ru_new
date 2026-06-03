<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class GenerateReviews extends Command
{
    protected $signature = 'reviews:generate';
    protected $description = 'Generate realistic reviews with timestamps';

    private $reviewTemplates = [
        // Короткие отзывы (1 строка)
        "Топ! UC за {time} секунд 🔥",
        "Моментально пришло, спасибо!",
        "Быстро и без проблем 👍",
        "Лучший сервис, рекомендую!",
        "Всё супер, буду брать ещё!",
        
        // Средние отзывы (2-3 строки)
        "Покупаю уже {count} раз, всегда быстро. Никаких проблем не было!",
        "UC пришли за {time} секунд. Очень доволен скоростью!",
        "Друзья советовали, не пожалел. Всё честно и быстро.",
        "Первый раз покупал здесь. UC зачислились моментально, рекомендую всем!",
        "Заказал пока в лобби ждал, пришли до начала матча. Огонь!",
        "Отличный сервис! Поддержка быстро ответила на все вопросы.",
        "Всё работает как часы. Покупаю только здесь уже {count} месяца.",
        
        // Длинные отзывы (4-5 строк)
        "Сначала сомневался, но решил попробовать. UC пришли за {time} секунд! Поддержка реально помогла разобраться с игровым ID. Теперь буду постоянным клиентом.",
        "Покупал на {amount}к рублей, всё прошло отлично за {time} секунд. Поддержка ответила за {support} минуты и помогла с оплатой. Очень доволен сервисом!",
        "Первый раз покупаю UC через интернет, боялся обмана. Но здесь всё честно - UC пришли за полминуты! Поддержка в телеграм быстро отвечает. Рекомендую!",
        "Отличный магазин! Покупаю уже {count} раз и ни разу не было проблем. Быстрая доставка за {time} секунд, удобная оплата, адекватная поддержка. Советую всем игрокам!",
        
        // Эмоциональные отзывы
        "Ребят, это просто космос! 🚀 UC за {time} сек, поддержка топ!",
        "Думал обман, но нет! Всё пришло мгновенно. Спасибо! 🙏",
        "Лучший сервис! Покупаю уже {count} раз, всегда доволен ⭐",
        "Быстро, безопасно, надёжно. Что ещё нужно? Рекомендую! 💯",
        
        // Сравнительные отзывы
        "Пробовал другие сервисы - этот лучший. UC за {time} секунд!",
        "До этого покупал в другом месте - тут в разы быстрее!",
        "Перешёл сюда с конкурентов. Небо и земля по скорости!",
        
        // Отзывы с деталями
        "Купил {amount}к UC, пришло за {time} секунд. Всё чётко!",
        "Заказ на {amount}к ₽ обработали моментально. Поддержка 5/5!",
        "Покупаю здесь постоянно. Уже {count} заказов - всё отлично!",
    ];

    private $timeVariants = [30, 40, 45, 50, 55, 60, 90];
    private $countVariants = [2, 3, 4, 5, 6, 7, 8, 10];
    private $supportVariants = [1, 2, 3, 5];
    private $amountVariants = [5, 10, 15, 20, 30, 40];

    public function handle()
    {
        // Получаем текущие отзывы из кеша
        $reviews = Cache::get('generated_reviews', []);
        
        // Удаляем старые отзывы (старше 2 часов)
        $reviews = array_filter($reviews, function($review) {
            return Carbon::parse($review['created_at'])->diffInMinutes(now()) < 120;
        });

        // Если отзывов меньше 8, генерируем новый
        if (count($reviews) < 8) {
            $newReview = $this->generateReview();
            $reviews[] = $newReview;
            
            $this->info("Generated new review: " . substr($newReview['text'], 0, 50) . "...");
        } else {
            // Удаляем самый старый отзыв
            array_shift($reviews);
            
            // Добавляем новый
            $newReview = $this->generateReview();
            $reviews[] = $newReview;
            
            $this->info("Replaced oldest review with new one");
        }

        // Сохраняем в кеш
        Cache::put('generated_reviews', $reviews, now()->addDays(1));
        
        $this->info("Total reviews in cache: " . count($reviews));
        
        return 0;
    }

    private function generateReview()
    {
        $template = $this->reviewTemplates[array_rand($this->reviewTemplates)];
        
        // Заменяем переменные
        $text = str_replace(
            ['{time}', '{count}', '{support}', '{amount}'],
            [
                $this->timeVariants[array_rand($this->timeVariants)],
                $this->countVariants[array_rand($this->countVariants)],
                $this->supportVariants[array_rand($this->supportVariants)],
                $this->amountVariants[array_rand($this->amountVariants)],
            ],
            $template
        );

        // Генерируем случайное время в пределах последних 90 минут
        $minutesAgo = rand(5, 90); // от 5 минут до 1.5 часов
        $createdAt = now()->subMinutes($minutesAgo);

        return [
            'text' => $text,
            'rating' => 5,
            'created_at' => $createdAt->toDateTimeString(),
            'time_ago' => $this->getTimeAgo($minutesAgo),
        ];
    }

    private function getTimeAgo($minutes)
    {
        if ($minutes < 60) {
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
            return '5 часов назад';
        }
    }
}
