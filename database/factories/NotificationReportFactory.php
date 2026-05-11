<?php

namespace Database\Factories;

use App\Enums\NotificationReportStatus;
use App\Models\NotificationReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationReport>
 */
class NotificationReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => NotificationReportStatus::Processing,
            'period_from' => now()->subDay(),
            'period_to' => now(),
            'disk' => 'local',
            'path' => null,
            'error' => null,
        ];
    }
}
