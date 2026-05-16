<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_reminders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('appointment_id')
                ->constrained('appointments')
                ->cascadeOnDelete();

            $table->enum('channel', ['email', 'sms']);
            $table->string('reminder_type')->default('24_hours_before');
            $table->enum('status', ['sent', 'failed']);

            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->unique(
                ['appointment_id', 'channel', 'reminder_type'],
                'appointment_reminders_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_reminders');
    }
};