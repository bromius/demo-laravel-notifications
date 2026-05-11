<?php

namespace App\Models;

use App\Enums\NotificationReportStatus;
use Database\Factories\NotificationReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property NotificationReportStatus $status
 * @property Carbon $period_from
 * @property Carbon $period_to
 * @property string $disk
 * @property string|null $path
 * @property string|null $error
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class NotificationReport extends Model
{
    /** @use HasFactory<NotificationReportFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'period_from',
        'period_to',
        'disk',
        'path',
        'error',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => NotificationReportStatus::class,
            'period_from' => 'datetime',
            'period_to' => 'datetime',
        ];
    }
}
