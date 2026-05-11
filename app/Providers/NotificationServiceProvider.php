<?php

namespace App\Providers;

use App\Contracts\NotificationChannel;
use App\Support\Notifications\NotificationChannelRegistry;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NotificationChannelRegistry::class, function (Application $app): NotificationChannelRegistry {
            /** @var array<class-string> $channelClasses */
            $channelClasses = config('notifications.channels', []);

            $channels = array_map(static function (string $channelClass) use ($app): NotificationChannel {
                $channel = $app->make($channelClass);

                if (! $channel instanceof NotificationChannel) {
                    throw new RuntimeException(__('Notification channel class must implement the channel contract.'));
                }

                return $channel;
            }, $channelClasses);

            return new NotificationChannelRegistry($channels);
        });
    }
}
