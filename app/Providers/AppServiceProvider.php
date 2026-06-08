<?php

namespace App\Providers;

use App\Helpers\Telegram;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Connection;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(\App\Helpers\Telegram::class, function ($app) {
            return new \App\Helpers\Telegram(
                new \Illuminate\Support\Facades\Http(),
                config('services.telegram.bot_token')
            );
        });
    }

    public function boot(): void
    {
        if (config('app.env') === 'production') {
            $this->optimizeQueries();
            $this->configureCache();
        }

        $this->shareCommonData();
    }

    private function optimizeQueries(): void
    {
        DB::listen(function ($query) {
            if ($query->time > 1000) {
                \Log::warning('Slow query detected', [
                    'sql' => $query->sql,
                    'time' => $query->time,
                    'bindings' => $query->bindings,
                ]);
            }
        });

        DB::connection()->enableQueryLog();
    }

    private function configureCache(): void
    {
        if (config('cache.default') === 'redis') {
            Cache::forever('app_booted_at', now());
        }
    }

    private function shareCommonData(): void
    {
        View::composer('*', function ($view) {
            $view->with('app_name', config('app.name'));
        });
    }
}
