<?php

namespace Database\Seeders;

use App\Enums\NotificationReportStatus;
use App\Models\NotificationReport;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class NotificationReportSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()
            ->where('email', UserSeeder::TEST_USER_EMAIL)
            ->firstOrFail();

        $periodFrom = CarbonImmutable::parse('2026-05-01 00:00:00');
        $periodTo = CarbonImmutable::parse('2026-05-10 23:59:59');
        $path = "reports/notifications/user-{$user->id}-seed-report.csv";

        Storage::disk('local')->put($path, "channel,total,failed\nemail,2,1\ntelegram,2,0\n");

        NotificationReport::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'period_from' => $periodFrom,
                'period_to' => $periodTo,
                'status' => NotificationReportStatus::Completed,
            ],
            [
                'disk' => 'local',
                'path' => $path,
                'error' => null,
            ],
        );

        NotificationReport::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'period_from' => $periodFrom->subDays(10),
                'period_to' => $periodTo->subDays(10),
                'status' => NotificationReportStatus::Failed,
            ],
            [
                'disk' => 'local',
                'path' => null,
                'error' => 'Report generation failed.',
            ],
        );
    }
}
