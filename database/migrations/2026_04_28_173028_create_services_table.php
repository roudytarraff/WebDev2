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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')
                ->constrained('service_categories')
                ->cascadeOnDelete();
                
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();

            $table->decimal('price',10,2);
            $table->integer('duration_minutes');

            $table->boolean('requires_appointment');
            $table->boolean('supports_online_payment');
            $table->boolean('supports_crypto_payment');

            $table->enum('status',['active','inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
