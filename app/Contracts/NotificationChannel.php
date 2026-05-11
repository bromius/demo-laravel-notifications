<?php

namespace App\Contracts;

use App\Models\Notification;

interface NotificationChannel
{
    public function name(): string;

    public function send(Notification $notification): void;
}
