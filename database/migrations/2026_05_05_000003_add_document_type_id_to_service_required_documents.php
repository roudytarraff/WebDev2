<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_required_documents', function (Blueprint $table) {
            $table->foreignId('document_type_id')->nullable()->after('service_id')->constrained('document_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_required_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('document_type_id');
        });
    }
};
