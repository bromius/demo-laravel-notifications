<?php

namespace App\Support\Notifications\Channels;

use App\Contracts\NotificationChannel;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class TelegramNotificationChannel implements NotificationChannel
{
    public function name(): string
    {
        return 'telegram';
    }

    public function send(Notification $notification): void
    {
        Log::info('Telegram notification stub sent.', [
            'notification_id' => $notification->id,
            'user_id' => $notification->user_id,
        ]);
    }
}
