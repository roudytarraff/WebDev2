<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('request_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('service_requests')->cascadeOnDelete();

            $table->enum('old_status',['pending','approved','rejected','in_progress','completed']);
            $table->enum('new_status',['pending','approved','rejected','in_progress','completed']);

            $table->foreignId('changed_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('changed_at');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_status_histories');
    }
};
