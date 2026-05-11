<?php

namespace App\Jobs;

use App\Models\NotificationReport;
use App\Services\Notifications\NotificationReportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class GenerateNotificationReportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public NotificationReport $report,
    ) {}

    public function handle(NotificationReportService $reports): void
    {
        $reports->generate($this->report->refresh());
    }

    public function tries(): int
    {
        return 3;
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function failed(Throwable $exception): void
    {
        app(NotificationReportService::class)->markAsFailed($this->report->refresh(), $exception);
    }
}
