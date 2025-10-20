<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->string('title')->nullable();   // e.g. "Google Ad Link"
            $table->string('type')->nullable();    // e.g. "google", "facebook", "custom"
            $table->string('url');                 // reward or ad link
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_rewards');
    }
};