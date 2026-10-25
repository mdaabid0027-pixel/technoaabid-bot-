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
        // 1. Add referral and spin fields to telegram_users
        Schema::table('telegram_users', function (Blueprint $table) {
            $table->foreignId('referrer_id')->nullable()->after('telegram_id')->constrained('telegram_users')->onDelete('set null');
            $table->timestamp('last_spin_at')->nullable()->after('balance');
            $table->integer('total_spins')->default(0)->after('last_spin_at');
            $table->decimal('total_spin_won', 10, 2)->default(0.00)->after('total_spins');
            $table->integer('referral_count')->default(0)->after('total_spin_won');
            $table->decimal('referral_earnings', 10, 2)->default(0.00)->after('referral_count');
        });

        // 2. Create spins log table
        Schema::create('spins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('telegram_user_id')->constrained('telegram_users')->onDelete('cascade');
            $table->decimal('amount_won', 10, 2);
            $table->string('reward_label');
            $table->timestamps();
        });

        // 3. Create referrals tracking table
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('telegram_users')->onDelete('cascade');
            $table->foreignId('referred_user_id')->constrained('telegram_users')->onDelete('cascade');
            $table->boolean('is_rewarded')->default(false);
            $table->decimal('reward_amount', 10, 2)->default(30.00);
            $table->timestamp('rewarded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('spins');
        Schema::table('telegram_users', function (Blueprint $table) {
            $table->dropForeign(['referrer_id']);
            $table->dropColumn([
                'referrer_id',
                'last_spin_at',
                'total_spins',
                'total_spin_won',
                'referral_count',
                'referral_earnings',
            ]);
        });
    }
};
