<?php

namespace App\Services\Notifications;

use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function create(User $user, string $channel, string $message): Notification
    {
        return DB::transaction(function () use ($user, $channel, $message): Notification {
            $notification = Notification::query()->create([
                'user_id' => $user->id,
                'channel' => $channel,
                'message' => $message,
                'status' => NotificationStatus::Processing,
            ]);

            SendNotificationJob::dispatch($notification)->afterCommit();

            return $notification;
        });
    }
}
