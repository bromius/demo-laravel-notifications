<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListUserNotificationsRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserNotificationController extends Controller
{
    public function index(ListUserNotificationsRequest $request, User $user): AnonymousResourceCollection
    {
        $query = Notification::query()
            ->where('user_id', $user->id)
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
        }

        if ($request->filled('channel')) {
            $query->where('channel', (string) $request->string('channel'));
        }

        return NotificationResource::collection(
            $query->paginate($request->integer('per_page', 15)),
        );
    }
}
