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
        Schema::create('dmca_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained()->onDelete('cascade');
            $table->foreignId('content_id')->constrained()->onDelete('cascade');
            $table->foreignId('monitoring_result_id')->nullable()->constrained()->onDelete('set null');
            $table->string('infringing_url', 2048);
            $table->string('original_url', 2048);
            $table->string('status', 30)->default('draft');
            $table->string('recipient_type', 50);
            $table->string('recipient_email')->nullable();
            $table->text('notice_content');
            $table->string('reference_number', 50)->unique();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolution', 50)->nullable();
            $table->text('response_notes')->nullable();
            $table->json('evidence_files')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['license_id', 'status']);
            $table->index('reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dmca_requests');
    }
};
