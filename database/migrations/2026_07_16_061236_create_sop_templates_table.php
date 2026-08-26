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
        Schema::create('sop_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('team_activity_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('source_sop_id')->nullable()->constrained('sop_documents')->nullOnDelete();
            $table->string('name');
            $table->string('template_code')->nullable();
            $table->text('description')->nullable();
            $table->json('template_payload');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sop_templates');
    }
};
