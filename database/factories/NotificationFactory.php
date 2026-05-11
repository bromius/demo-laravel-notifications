<?php

namespace Database\Factories;

use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'channel' => fake()->randomElement(['email', 'telegram']),
            'message' => fake()->sentence(),
            'status' => NotificationStatus::Processing,
            'attempts' => 0,
            'last_error' => null,
            'sent_at' => null,
        ];
    }
}
