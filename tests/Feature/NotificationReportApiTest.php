<?php

namespace Tests\Feature;

use App\Enums\NotificationReportStatus;
use App\Jobs\GenerateNotificationReportJob;
use App\Models\NotificationReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotificationReportApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_report_generation_can_be_requested(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $response = $this->postJson("/api/users/{$user->id}/notification-reports", [
            'period_from' => now()->subDay()->toISOString(),
            'period_to' => now()->toISOString(),
        ]);

        $response
            ->assertAccepted()
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.status', NotificationReportStatus::Processing->value);

        $this->assertDatabaseHas('notification_reports', [
            'user_id' => $user->id,
            'status' => NotificationReportStatus::Processing->value,
        ]);

        Queue::assertPushed(GenerateNotificationReportJob::class);
    }

    public function test_notification_report_status_can_be_read(): void
    {
        $report = NotificationReport::factory()->create([
            'status' => NotificationReportStatus::Failed,
            'error' => 'Generation failed.',
        ]);

        $this->getJson("/api/notification-reports/{$report->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $report->id)
            ->assertJsonPath('data.status', NotificationReportStatus::Failed->value)
            ->assertJsonPath('data.error', 'Generation failed.');
    }

    public function test_completed_notification_report_can_be_downloaded(): void
    {
        Storage::fake('local');

        $path = 'reports/notifications/report.csv';
        Storage::disk('local')->put($path, "channel,total,failed\nemail,2,1\n");

        $report = NotificationReport::factory()->create([
            'status' => NotificationReportStatus::Completed,
            'disk' => 'local',
            'path' => $path,
        ]);

        $this->get("/api/notification-reports/{$report->id}/download")
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_unfinished_notification_report_cannot_be_downloaded(): void
    {
        $report = NotificationReport::factory()->create([
            'status' => NotificationReportStatus::Processing,
        ]);

        $this->getJson("/api/notification-reports/{$report->id}/download")
            ->assertStatus(409)
            ->assertJsonPath('message', __('Report is not ready.'));
    }
}
