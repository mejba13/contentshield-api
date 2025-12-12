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
        Schema::create('monitoring_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained()->onDelete('cascade');
            $table->foreignId('license_id')->constrained()->onDelete('cascade');
            $table->string('found_url', 2048);
            $table->string('found_domain');
            $table->decimal('similarity_score', 5, 2);
            $table->string('match_type', 50);
            $table->text('matched_excerpt')->nullable();
            $table->string('detection_method', 50);
            $table->string('status', 20)->default('new');
            $table->boolean('is_false_positive')->default(false);
            $table->timestamp('detected_at');
            $table->timestamp('resolved_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['license_id', 'status']);
            $table->index(['content_id', 'detected_at']);
            $table->index('found_domain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_results');
    }
};
