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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();

            $table->foreignId('citizen_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();

            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status',['pending','approved','rejected','in_progress','completed']);

            $table->text('description')->nullable();
            $table->string('qr_code')->nullable()->unique();
            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
