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
        Schema::create('activations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained()->onDelete('cascade');
            $table->string('site_url');
            $table->string('site_hash', 64);
            $table->string('plugin_version', 20)->nullable();
            $table->timestamp('activated_at');
            $table->timestamp('last_check')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['license_id', 'site_url']);
            $table->index('site_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activations');
    }
};
