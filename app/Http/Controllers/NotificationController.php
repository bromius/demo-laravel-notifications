<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function store(StoreNotificationRequest $request, NotificationService $notifications): JsonResponse
    {
        $notification = $notifications->create(
            User::query()->findOrFail($request->integer('user_id')),
            (string) $request->string('channel'),
            (string) $request->string('message'),
        );

        return NotificationResource::make($notification)
            ->response()
            ->setStatusCode(202);
    }

    public function show(Notification $notification): NotificationResource
    {
        return NotificationResource::make($notification);
    }
}
