<?php

namespace App\Jobs;

use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Services\Notifications\NotificationDeliveryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Notification $notification,
    ) {}

    public function handle(NotificationDeliveryService $delivery): void
    {
        $notification = $this->notification->refresh();

        if ($notification->status === NotificationStatus::Sent) {
            return;
        }

        $delivery->deliver($notification);
    }

    public function tries(): int
    {
        return (int) config('notifications.delivery.tries', 3);
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        /** @var list<int> $backoff */
        $backoff = config('notifications.delivery.backoff', [10, 60, 300]);

        return $backoff;
    }

    public function failed(Throwable $exception): void
    {
        app(NotificationDeliveryService::class)->markAsFailed($this->notification->refresh(), $exception);
    }
}
