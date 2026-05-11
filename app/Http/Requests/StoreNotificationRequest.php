<?php

namespace App\Http\Requests;

use App\Support\Notifications\NotificationChannelRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNotificationRequest extends FormRequest
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'channel' => ['required', 'string', 'max:50', Rule::in($channels->names())],
            'message' => ['required', 'string', 'max:500'],
        ];
    }
}
