<?php

namespace App\Exceptions;

use App\Helpers\Telegram;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Contracts\Container\Container;
use Throwable;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    protected $telegram;

    public function __construct(Container $container, Telegram $telegram)
    {
        parent::__construct($container);
        $this->telegram = $telegram;
    }

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            
        });
    }

    public function report(Throwable $e)
    {
        
        parent::report($e);

        try {
            $data = [
                'description' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];

          
            $message = view('report', $data)->render();

        
            $chatId = config('services.telegram.chat_id');

            $this->telegram->sendMessage($chatId, $message);
        } catch (Throwable $ex) {
            Log::error('Ошибка при отправке отчета в Telegram: ' . $ex->getMessage());
        }
    }
}
