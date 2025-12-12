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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained()->onDelete('cascade');
            $table->foreignId('activation_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('post_id');
            $table->string('post_url', 2048);
            $table->string('post_title', 500);
            $table->string('fingerprint', 128);
            $table->string('content_hash', 64);
            $table->text('watermark_data')->nullable();
            $table->integer('word_count')->default(0);
            $table->string('status', 20)->default('active');
            $table->boolean('monitoring_enabled')->default(true);
            $table->timestamp('last_monitored_at')->nullable();
            $table->timestamps();

            $table->index(['license_id', 'status']);
            $table->index('fingerprint');
            $table->index('content_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
