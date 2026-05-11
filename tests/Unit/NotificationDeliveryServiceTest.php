<?php

namespace Tests\Unit;

use App\Contracts\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Services\Notifications\NotificationDeliveryService;
use App\Support\Notifications\NotificationChannelRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class NotificationDeliveryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_is_marked_as_sent_after_successful_delivery(): void
    {
        $notification = Notification::factory()->create([
            'channel' => 'email',
            'status' => NotificationStatus::Processing,
        ]);

        $service = new NotificationDeliveryService(new NotificationChannelRegistry([
            new class implements NotificationChannel
            {
                public function name(): string
                {
                    return 'email';
                }

                public function send(Notification $notification): void {}
            },
        ]));

        $service->deliver($notification);

        $notification->refresh();

        $this->assertSame(NotificationStatus::Sent, $notification->status);
        $this->assertSame(1, $notification->attempts);
        $this->assertNull($notification->last_error);
        $this->assertNotNull($notification->sent_at);
    }

    public function test_delivery_error_is_stored_and_can_be_marked_as_failed(): void
    {
        $notification = Notification::factory()->create([
            'channel' => 'email',
            'status' => NotificationStatus::Processing,
        ]);

        $service = new NotificationDeliveryService(new NotificationChannelRegistry([
            new class implements NotificationChannel
            {
                public function name(): string
                {
                    return 'email';
                }

                public function send(Notification $notification): void
                {
                    throw new RuntimeException('Transport failed.');
                }
            },
        ]));

        try {
            $service->deliver($notification);
            $this->fail('Expected delivery exception was not thrown.');
        } catch (RuntimeException $exception) {
            $service->markAsFailed($notification->refresh(), $exception);
        }

        $notification->refresh();

        $this->assertSame(NotificationStatus::Failed, $notification->status);
        $this->assertSame(1, $notification->attempts);
        $this->assertSame('Transport failed.', $notification->last_error);
    }
}
