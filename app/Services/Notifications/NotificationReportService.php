<?php

namespace App\Services\Notifications;

use App\Enums\NotificationReportStatus;
use App\Enums\NotificationStatus;
use App\Jobs\GenerateNotificationReportJob;
use App\Models\Notification;
use App\Models\NotificationReport;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class NotificationReportService
{
    public function request(User $user, CarbonInterface $periodFrom, CarbonInterface $periodTo): NotificationReport
    {
        $disk = $this->disk();

        return DB::transaction(function () use ($user, $periodFrom, $periodTo, $disk): NotificationReport {
            $report = NotificationReport::query()->create([
                'user_id' => $user->id,
                'status' => NotificationReportStatus::Processing,
                'period_from' => $periodFrom,
                'period_to' => $periodTo,
                'disk' => $disk,
            ]);

            GenerateNotificationReportJob::dispatch($report)->afterCommit();

            return $report;
        });
    }

    public function generate(NotificationReport $report): void
    {
        try {
            $rows = Notification::query()
                ->selectRaw('channel, COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failed', [
                    NotificationStatus::Failed->value,
                ])
                ->where('user_id', $report->user_id)
                ->whereBetween('created_at', [$report->period_from, $report->period_to])
                ->groupBy('channel')
                ->orderBy('channel')
                ->get();

            $path = $this->path($report);
            $temporaryPath = $path.'.tmp';
            $disk = Storage::disk($this->disk());

            $disk->put($temporaryPath, $this->toCsv($rows));
            $disk->move($temporaryPath, $path);

            $report->forceFill([
                'status' => NotificationReportStatus::Completed,
                'disk' => $this->disk(),
                'path' => $path,
                'error' => null,
            ])->save();
        } catch (Throwable $exception) {
            $this->markAsFailed($report, $exception);

            throw $exception;
        }
    }

    public function markAsFailed(NotificationReport $report, Throwable $exception): void
    {
        $report->forceFill([
            'status' => NotificationReportStatus::Failed,
            'error' => $exception->getMessage(),
        ])->save();
    }

    private function disk(): string
    {
        return (string) config('notifications.reports.disk', 'local');
    }

    private function directory(): string
    {
        return trim((string) config('notifications.reports.directory', 'reports/notifications'), '/');
    }

    private function path(NotificationReport $report): string
    {
        return sprintf('%s/user-%d-report-%d.csv', $this->directory(), $report->user_id, $report->id);
    }

    /**
     * @param  iterable<object{channel: string, total: int|string, failed: int|string|null}>  $rows
     */
    private function toCsv(iterable $rows): string
    {
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            return '';
        }

        fputcsv($stream, ['channel', 'total', 'failed']);

        foreach ($rows as $row) {
            fputcsv($stream, [
                $row->channel,
                (int) $row->total,
                (int) $row->failed,
            ]);
        }

        rewind($stream);

        $contents = stream_get_contents($stream);
        fclose($stream);

        return $contents === false ? '' : $contents;
    }
}
