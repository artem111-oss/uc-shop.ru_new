<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║          Initial Reviews Generation                              ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

// Очищаем старый кэш
Cache::forget('generated_reviews');
echo "🧹 Cleared old reviews cache\n\n";

// Генерируем 12 начальных отзывов (для надежности)
for ($i = 0; $i < 12; $i++) {
    Artisan::call('reviews:generate');
    echo "✅ Generated review " . ($i + 1) . "/12\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════════\n";
echo "✅ Successfully generated 12 initial reviews\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

// Показываем статистику
$reviews = Cache::get('generated_reviews', []);
echo "Total reviews in cache: " . count($reviews) . "\n\n";

if (count($reviews) > 0) {
    echo "First 3 reviews:\n";
    foreach (array_slice($reviews, 0, 3) as $index => $review) {
        echo "\n" . ($index + 1) . ". " . $review['text'] . "\n";
        echo "   Time: " . $review['time_ago'] . "\n";
    }
}

echo "\n✅ Done! Reviews are now available via API: /api/reviews\n\n";
