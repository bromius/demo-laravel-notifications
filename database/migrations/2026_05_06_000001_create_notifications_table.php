<?php

use App\Enums\NotificationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 50);
            $table->string('message', 500);
            $table->string('status', 20)->default(NotificationStatus::Processing->value);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'id']);
            $table->index(['user_id', 'status', 'id']);
            $table->index(['user_id', 'channel', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
