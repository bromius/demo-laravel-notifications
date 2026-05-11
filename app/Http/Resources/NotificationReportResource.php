<?php

namespace App\Http\Resources;

use App\Models\NotificationReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationReportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var NotificationReport $report */
        $report = $this->resource;

        return [
            'id' => $report->id,
            'user_id' => $report->user_id,
            'status' => $report->status->value,
            'period_from' => $report->period_from,
            'period_to' => $report->period_to,
            'path' => $report->path,
            'error' => $report->error,
            'created_at' => $report->created_at,
            'updated_at' => $report->updated_at,
        ];
    }
}
