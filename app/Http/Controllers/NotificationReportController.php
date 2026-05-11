<?php

namespace App\Http\Controllers;

use App\Enums\NotificationReportStatus;
use App\Http\Requests\StoreNotificationReportRequest;
use App\Http\Resources\NotificationReportResource;
use App\Models\NotificationReport;
use App\Models\User;
use App\Services\Notifications\NotificationReportService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotificationReportController extends Controller
{
    public function store(
        StoreNotificationReportRequest $request,
        User $user,
        NotificationReportService $reports,
    ): JsonResponse {
        $report = $reports->request(
            $user,
            CarbonImmutable::parse((string) $request->string('period_from')),
            CarbonImmutable::parse((string) $request->string('period_to')),
        );

        return NotificationReportResource::make($report)
            ->response()
            ->setStatusCode(202);
    }

    public function show(NotificationReport $report): NotificationReportResource
    {
        return NotificationReportResource::make($report);
    }

    public function download(NotificationReport $report): StreamedResponse|JsonResponse
    {
        if ($report->status !== NotificationReportStatus::Completed || $report->path === null) {
            return response()->json([
                'message' => __('Report is not ready.'),
            ], 409);
        }

        $disk = Storage::disk($report->disk);

        if (! $disk->exists($report->path)) {
            return response()->json([
                'message' => __('Report file was not found.'),
            ], 404);
        }

        return $disk->download($report->path, basename($report->path));
    }
}
