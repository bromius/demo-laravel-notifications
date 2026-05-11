<?php

namespace App\Support\Notifications\Channels;

use App\Contracts\NotificationChannel;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class EmailNotificationChannel implements NotificationChannel
{
    public function name(): string
    {
        return 'email';
    }

    public function send(Notification $notification): void
    {
        Log::info('Email notification stub sent.', [
            'notification_id' => $notification->id,
            'user_id' => $notification->user_id,
        ]);
    }
}
