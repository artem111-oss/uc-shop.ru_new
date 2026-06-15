<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Telegram
{

  protected $http;
  protected $bot;
  const url = 'https://api.telegram.org/bot'; // fallback only

  public function __construct(Http $http, $bot = null)
  {
    $this->http = $http;
    $this->bot = $bot;
  }

  public function sendMessage($chat_id, $message)
{
    $options = [];
    if (config('services.telegram.proxy')) {
        $options['proxy'] = config('services.telegram.proxy');
    }

    return $this->http::withOptions($options)
        ->post(self::url . $this->bot . '/sendMessage', [
            'chat_id'    => $chat_id,
            'text'       => $message,
            'parse_mode' => 'html'
        ]);
}

  public function sendDocument($chat_id, $file, $reply_id)
  {
    return $this->http::attach('document', Storage::get('/public/.$file'), 'document.png')->post(self::url . $this->bot . '/sendDocument', [
      'chat_id' => $chat_id,
      'reply_ti_message_id' => $reply_id
    ]);
  }

  public function sendButtons($chat_id, $message, $buttons)
{
    $options = [];
    if (config('services.telegram.proxy')) {
        $options['proxy'] = config('services.telegram.proxy');
    }

    return $this->http::withOptions($options)
        ->post(self::url . $this->bot . '/sendMessage', [
            'chat_id'      => $chat_id,
            'text'         => $message,
            'parse_mode'   => 'html',
            'reply_markup' => $buttons
        ]);
}
}