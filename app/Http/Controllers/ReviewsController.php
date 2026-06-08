<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

class ReviewsController extends Controller
{
    private array $authors = [
    'Арсен_PM', 'killer_rus', 'TigerX99', 'ProPlayer_47', 'Denis_UFA',
    'nightwolf_pm', 'AlexKing', 'Стас228', 'FoxHunter', 'vlad_pubg',
    'Максим_К', 'dark_sniper', 'R1CHBOY', 'ПабГ_Фанат', 'Коля_топ',
    'zero_cool99', 'MikeStrike', 'Серёга_UZ', 'bestshot_ru', 'GameOver77',
    'Димас_pro', 'shadow_kill', 'NightRaider', 'Кирилл228', 'AimGod_rf',
    'Руслан_KZ', 'blaze_one', 'TopFragger', 'Женёк_ufa', 'snipe_king',
    'Артём_PG', 'coldblood99', 'X_Hunter_X', 'ВаняПабг', 'FastFingers',
    'Тимур_pro', 'reaper_rus', 'M416_King', 'Илья_777', 'ghost_mode',
    'Pavel_pm', 'ironwall_rf', 'QuickScope', 'Борис_играет', 'ace_shot',
    'Никита_top', 'stealthx99', 'RushBOnly', 'Паша_UFA', 'dropzone_ru',
    'Егор_kill', 'headshot_hz', 'SkyDiver_1', 'Лёха_pubg', 'omega_fire',
    'Степан_pm', 'noscope_bro', 'CarryKing', 'Данил_pro', 'viper_shot',
    'Ромка_228', 'flashpoint9', 'UltraFrags', 'Глеб_играет', 'rapid_fire',
    'Тёма_rus', 'coldsnipe_r', 'WildHunter', 'Женя_пабг', 'zone_ctrl',
    ];

    private array $products = [
        '60 UC', '180 UC', '325 UC', '385 UC', '660 UC', '720 UC', '985 UC', 
        '1320 UC', '1800 UC', '1920 UC', '2460 UC', '3850 UC', '5650 UC', '8100 UC',
        '9900 UC', '12010 UC', '16200 UC', 'UC на Royal Pass', 'UC для скина',
        'UC другу в подарок', 'UC на новую рулетку', 'большой пакет',
    ];

    private array $payments = [
        'через СБП', 'через Сбер', 'через Т-Банк', 'через Альфа', 'через ВТБ',
        'картой', 'по QR-коду',
    ];

    private array $speeds = [
        'за 20 секунд', 'за 30 секунд', 'за 40 секунд', 'за минуту',
        'буквально за полминуты', 'пока я ещё на сайте сидел',
        'быстрее чем ожидал', 'моментально',
    ];

    private array $templates = [
        'Взял {product}, пришло {speed}. Оплатил {payment} — никаких проблем. Рекомендую.',
        'Заказал {product} {payment}. UC появились {speed}. Теперь только здесь беру.',
        'Оплатил {payment}, взял {product}. Всё пришло {speed}. Никаких паролей не спрашивали — безопасно.',
        'Покупал {product} уже несколько раз. Всегда {speed}. Платил {payment}, работает чётко.',
        'Взял {product}. Переживал первый раз, но UC пришли {speed}. Оплата {payment} прошла без проблем.',
        'Нужен был {product} срочно. Оплатил {payment} — получил {speed}. Топ сервис.',
        '{product} купил {payment}. Пришло {speed}, даже удивился. Буду брать ещё.',
        'Брал {product} {payment}. Никаких задержек, всё {speed}. Сайт удобный, всё понятно.',
        'Давно ищу нормальный магазин UC. Нашёл здесь. Купил {product} {payment} — пришло {speed}.',
        'Уже 4-й раз покупаю. На этот раз {product} {payment}. Всё как всегда — {speed}.',
        'Попробовал впервые — взял {product}. Оплата {payment} прошла мгновенно, UC {speed}. Огонь.',
        '{product} {payment} — пришло {speed}. Без регистрации, без лишних вопросов. Именно так и нужно.',
        'Брал {product} на себя и другу. Оба заказа {payment} — оба пришли {speed}. Красава.',
        'Купил {product}. Первый раз платил {payment} — немного переживал. Но всё {speed}. Спасибо!',
        'Нашёл по совету друга. Взял {product} {payment}, получил {speed}. Теперь сам советую.',
    ];

    private array $timeVariants = [
        'только что', '1 мин назад', '2 мин назад', '3 мин назад', '5 мин назад',
        '7 мин назад', '9 мин назад', '12 мин назад', '16 мин назад', '21 мин назад',
        '27 мин назад', '33 мин назад', '41 мин назад', '49 мин назад', '57 мин назад',
        '1 час назад', '1 час назад', '2 часа назад', '2 часа назад', '3 часа назад',
    ];

    public function getReviews()
    {
        $reviews = Cache::remember('reviews_generated', 420, function () {
            return $this->generateReviews(6);
        });

        return response()->json([
            'success' => true,
            'reviews' => $reviews,
            'count'   => count($reviews),
        ]);
    }

    private function generateReviews(int $count): array
    {
        $templates = $this->templates;
        $authors   = $this->authors;
        $times     = $this->timeVariants;

        shuffle($templates);
        shuffle($authors);
        shuffle($times);

        $selectedTimes = array_slice($times, 0, $count);
        sort($selectedTimes);
        $selectedTimes = array_reverse($selectedTimes);

        $reviews = [];
        for ($i = 0; $i < $count; $i++) {
            $text = str_replace(
                ['{product}', '{payment}', '{speed}'],
                [
                    $this->pick($this->products),
                    $this->pick($this->payments),
                    $this->pick($this->speeds),
                ],
                $templates[$i % count($templates)]
            );

            $reviews[] = [
                'author'   => $authors[$i % count($authors)],
                'text'     => $text,
                'rating'   => (rand(1, 100) <= 85) ? 5 : 4,
                'time_ago' => $selectedTimes[$i],
            ];
        }

        return $reviews;
    }

    private function pick(array $arr): string
    {
        return $arr[array_rand($arr)];
    }
}