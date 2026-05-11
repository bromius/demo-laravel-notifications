<?php

namespace App\Enums;

enum NotificationReportStatus: string
{
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
}
