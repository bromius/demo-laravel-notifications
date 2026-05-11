<?php

namespace App\Support\Notifications;

use App\Contracts\NotificationChannel;
use InvalidArgumentException;

class NotificationChannelRegistry
{
    /** @var array<string, NotificationChannel> */
    private array $channels = [];

    /**
     * @param  iterable<NotificationChannel>  $channels
     */
    public function __construct(iterable $channels)
    {
        foreach ($channels as $channel) {
            $this->channels[$channel->name()] = $channel;
        }
    }

    public function get(string $name): NotificationChannel
    {
        return $this->channels[$name]
            ?? throw new InvalidArgumentException(__('Notification channel is not supported.'));
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_keys($this->channels);
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->channels);
    }
}
