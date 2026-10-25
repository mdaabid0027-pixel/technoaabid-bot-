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
        Schema::table('keys', function (Blueprint $table) {
            if (!Schema::hasColumn('keys', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('sold_at');
            }
            if (!Schema::hasColumn('keys', 'renewal_alert_sent_at')) {
                $table->timestamp('renewal_alert_sent_at')->nullable()->after('expires_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keys', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'renewal_alert_sent_at']);
        });
    }
};
