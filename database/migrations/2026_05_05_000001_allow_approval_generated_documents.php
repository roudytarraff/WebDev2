<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE generated_documents MODIFY document_type ENUM('certificate','receipt','report','approval')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE generated_documents MODIFY document_type ENUM('certificate','receipt','report')");
    }
};
