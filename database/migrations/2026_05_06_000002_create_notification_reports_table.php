<?php

use App\Enums\NotificationReportStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default(NotificationReportStatus::Processing->value);
            $table->timestamp('period_from');
            $table->timestamp('period_to');
            $table->string('disk', 50)->default('local');
            $table->string('path')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'id']);
            $table->index(['user_id', 'status', 'id']);
            $table->index(['user_id', 'period_from', 'period_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_reports');
    }
};
