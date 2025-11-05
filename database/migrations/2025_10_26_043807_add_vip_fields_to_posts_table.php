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
           Schema::table('posts', function (Blueprint $table) {
                $table->boolean('is_vip')->default(false)->after('body');
                $table->unsignedInteger('required_points')->default(0)->after('is_vip');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['is_vip', 'required_points']);
        });
    }
};
