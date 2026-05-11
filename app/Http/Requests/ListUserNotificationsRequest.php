<?php

namespace App\Http\Requests;

use App\Enums\NotificationStatus;
use App\Support\Notifications\NotificationChannelRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListUserNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(NotificationChannelRegistry $channels): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::enum(NotificationStatus::class)],
            'channel' => ['sometimes', 'string', 'max:50', Rule::in($channels->names())],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
