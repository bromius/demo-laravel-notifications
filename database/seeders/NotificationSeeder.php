<?php

namespace Database\Seeders;

use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()
            ->where('email', UserSeeder::TEST_USER_EMAIL)
            ->firstOrFail();

        $notifications = [
            [
                'channel' => 'email',
                'message' => 'Your email notification was delivered.',
                'status' => NotificationStatus::Sent,
                'attempts' => 1,
                'last_error' => null,
                'sent_at' => now()->subHours(3),
            ],
            [
                'channel' => 'telegram',
                'message' => 'Your telegram notification is waiting for delivery.',
                'status' => NotificationStatus::Processing,
                'attempts' => 0,
                'last_error' => null,
                'sent_at' => null,
            ],
            [
                'channel' => 'email',
                'message' => 'Your email notification could not be delivered.',
                'status' => NotificationStatus::Failed,
                'attempts' => 3,
                'last_error' => 'Provider timeout.',
                'sent_at' => null,
            ],
            [
                'channel' => 'telegram',
                'message' => 'Your telegram notification was delivered.',
                'status' => NotificationStatus::Sent,
                'attempts' => 1,
                'last_error' => null,
                'sent_at' => now()->subHour(),
            ],
        ];

        foreach ($notifications as $notification) {
            Notification::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'channel' => $notification['channel'],
                    'message' => $notification['message'],
                ],
                [
                    'status' => $notification['status'],
                    'attempts' => $notification['attempts'],
                    'last_error' => $notification['last_error'],
                    'sent_at' => $notification['sent_at'],
                ],
            );
        }
    }
}
