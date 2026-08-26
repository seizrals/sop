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
        Schema::create('sop_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('root_document_id')->nullable()->constrained('sop_documents')->nullOnDelete();
            $table->foreignId('parent_document_id')->nullable()->constrained('sop_documents')->nullOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('sop_number')->nullable();
            $table->string('title');
            $table->year('year');
            $table->unsignedInteger('revision_number')->default(0);
            $table->enum('status', ['draft', 'simpan', 'revisi', 'final'])->default('draft');
            $table->date('creation_date')->nullable();
            $table->date('revision_date')->nullable();
            $table->date('effective_date')->nullable();
            $table->string('approval_position')->nullable();
            $table->string('approval_name')->nullable();
            $table->string('approval_nip')->nullable();
            $table->json('legal_basis')->nullable();
            $table->json('executor_qualifications')->nullable();
            $table->json('related_documents')->nullable();
            $table->json('equipment')->nullable();
            $table->json('warnings')->nullable();
            $table->json('recording')->nullable();
            $table->json('executors')->nullable();
            $table->json('activities')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sop_documents');
    }
};
