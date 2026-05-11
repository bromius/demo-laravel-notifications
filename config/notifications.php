<?php

use App\Support\Notifications\Channels\EmailNotificationChannel;
use App\Support\Notifications\Channels\TelegramNotificationChannel;

return [
    'channels' => [
        EmailNotificationChannel::class,
        TelegramNotificationChannel::class,
    ],

    'delivery' => [
        'tries' => 6,
        'backoff' => [60, 600, 3600, 42000, 86400],
    ],

    'reports' => [
        'disk' => 'local',
        'directory' => 'reports/notifications',
    ],
];
