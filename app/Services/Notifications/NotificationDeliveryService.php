<?php

namespace App\Services\Notifications;

use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Support\Notifications\NotificationChannelRegistry;
use Throwable;

class NotificationDeliveryService
{
    public function __construct(
        private readonly NotificationChannelRegistry $channels,
    ) {}

    public function deliver(Notification $notification): void
    {
        $notification->forceFill([
            'attempts' => $notification->attempts + 1,
            'last_error' => null,
        ])->save();

        try {
            $this->channels->get($notification->channel)->send($notification);
        } catch (Throwable $exception) {
            $notification->forceFill([
                'last_error' => $exception->getMessage(),
            ])->save();

            throw $exception;
        }

        $notification->forceFill([
            'status' => NotificationStatus::Sent,
            'sent_at' => now(),
            'last_error' => null,
        ])->save();
    }

    public function markAsFailed(Notification $notification, Throwable $exception): void
    {
        $notification->forceFill([
            'status' => NotificationStatus::Failed,
            'last_error' => $exception->getMessage(),
        ])->save();
    }
}
