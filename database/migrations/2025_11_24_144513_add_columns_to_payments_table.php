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
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'subscription_id')) {
                $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade')->after('id');
            }
            if (!Schema::hasColumn('payments', 'amount')) {
                $table->decimal('amount', 10, 2)->after('subscription_id');
            }
            if (!Schema::hasColumn('payments', 'method')) {
                $table->enum('method', ['cash', 'transfer', 'qris', 'other'])->default('transfer')->after('amount');
            }
            if (!Schema::hasColumn('payments', 'status')) {
                $table->enum('status', ['pending', 'paid', 'failed'])->default('pending')->after('method');
            }
            if (!Schema::hasColumn('payments', 'paid_at')) {
                $table->datetime('paid_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('payments', 'proof_url')) {
                $table->string('proof_url', 255)->nullable()->after('paid_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'proof_url')) {
                $table->dropColumn('proof_url');
            }
            if (Schema::hasColumn('payments', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
            if (Schema::hasColumn('payments', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('payments', 'method')) {
                $table->dropColumn('method');
            }
            if (Schema::hasColumn('payments', 'amount')) {
                $table->dropColumn('amount');
            }
            if (Schema::hasColumn('payments', 'subscription_id')) {
                $table->dropForeign(['subscription_id']);
                $table->dropColumn('subscription_id');
            }
        });
    }
};
