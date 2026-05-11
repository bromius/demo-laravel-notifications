<?php

namespace Tests\Feature;

use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_can_be_created(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $response = $this->postJson('/api/notifications', [
            'user_id' => $user->id,
            'channel' => 'email',
            'message' => 'Your order was shipped.',
        ]);

        $response
            ->assertAccepted()
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.channel', 'email')
            ->assertJsonPath('data.status', NotificationStatus::Processing->value);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'channel' => 'email',
            'message' => 'Your order was shipped.',
            'status' => NotificationStatus::Processing->value,
        ]);

        Queue::assertPushed(SendNotificationJob::class);
    }

    public function test_notification_status_can_be_read(): void
    {
        $notification = Notification::factory()->create([
            'status' => NotificationStatus::Sent,
            'channel' => 'telegram',
        ]);

        $this->getJson("/api/notifications/{$notification->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $notification->id)
            ->assertJsonPath('data.status', NotificationStatus::Sent->value)
            ->assertJsonPath('data.channel', 'telegram');
    }

    public function test_user_notification_history_can_be_filtered(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $matchingNotification = Notification::factory()->create([
            'user_id' => $user->id,
            'channel' => 'telegram',
            'status' => NotificationStatus::Failed,
        ]);

        Notification::factory()->create([
            'user_id' => $user->id,
            'channel' => 'email',
            'status' => NotificationStatus::Failed,
        ]);

        Notification::factory()->create([
            'user_id' => $otherUser->id,
            'channel' => 'telegram',
            'status' => NotificationStatus::Failed,
        ]);

        $this->getJson("/api/users/{$user->id}/notifications?status=failed&channel=telegram")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingNotification->id);
    }
}
