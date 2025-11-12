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
            Schema::table('users', function (Blueprint $table) {
                $table->string('referral_code')->unique()->nullable();
                 $table->string('referred_by')->nullable()->index();
                $table->boolean('referral_rewarded')->default(false);
                $table->decimal('points', 10, 2)->default(0)->change();
            });
        }

        public function down(): void
        {
            Schema::table('users', function (Blueprint $table) {
                 $table->dropColumn(['referral_code', 'referred_by']);
                $table->dropColumn('referral_rewarded');
                $table->integer('points')->default(0)->change();
            });
        }
};
