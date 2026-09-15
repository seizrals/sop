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
        Schema::table('sop_documents', function (Blueprint $table) {
            $table->string('signed_file_path')->nullable()->after('notes');
            $table->string('signed_file_name')->nullable()->after('signed_file_path');
            $table->timestamp('signed_at')->nullable()->after('signed_file_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sop_documents', function (Blueprint $table) {
            $table->dropColumn(['signed_file_path', 'signed_file_name', 'signed_at']);
        });
    }
};
