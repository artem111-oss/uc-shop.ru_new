<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

Artisan::command('sitemap:generate', function () {
    Sitemap::create()
        ->add(Url::create('/')
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(1.0))
        ->add(Url::create('/contacts')
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
            ->setPriority(0.5))
        ->writeToFile(public_path('sitemap.xml'));

    $this->info('Sitemap обновлён.');
})->purpose('Генерация sitemap.xml');
