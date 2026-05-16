<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citizen_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('citizen_profiles', 'id_document_hash')) {
                $table->string('id_document_hash', 64)->nullable()->after('id_document_path');
                $table->index('id_document_hash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('citizen_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('citizen_profiles', 'id_document_hash')) {
                $table->dropIndex(['id_document_hash']);
                $table->dropColumn('id_document_hash');
            }
        });
    }
};