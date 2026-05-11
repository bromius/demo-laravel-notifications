<?php

namespace Tests\Unit;

use App\Contracts\NotificationChannel;
use App\Models\Notification;
use App\Support\Notifications\NotificationChannelRegistry;
use InvalidArgumentException;
use Tests\TestCase;

class NotificationChannelRegistryTest extends TestCase
{
    public function test_channel_can_be_resolved_by_name(): void
    {
        $channel = new class implements NotificationChannel
        {
            public function name(): string
            {
                return 'sms';
            }

            public function send(Notification $notification): void {}
        };

        $registry = new NotificationChannelRegistry([$channel]);

        $this->assertTrue($registry->has('sms'));
        $this->assertSame(['sms'], $registry->names());
        $this->assertSame($channel, $registry->get('sms'));
    }

    public function test_unknown_channel_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new NotificationChannelRegistry([]))->get('unknown');
    }
}
