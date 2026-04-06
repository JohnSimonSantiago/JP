<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PushNotificationService
{
    public static function send($pushToken, $title, $body, $data = [])
    {
        if (!$pushToken) return;

        Http::post('https://exp.host/--/api/v2/push/send', [
            'to'    => $pushToken,
            'title' => $title,
            'body'  => $body,
            'data'  => $data,
            'sound' => 'default',
        ]);
    }
}