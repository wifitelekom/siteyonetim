<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('charges', function (Blueprint $table) {
            $table->index(['site_id', 'apartment_id']);
            $table->index(['site_id', 'period']);
            $table->index(['site_id', 'due_date']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['site_id', 'expense_date']);
            $table->index(['site_id', 'vendor_id']);
        });

        Schema::table('receipts', function (Blueprint $table) {
            $table->index(['site_id', 'paid_at']);
            $table->index(['site_id', 'apartment_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['site_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::table('charges', function (Blueprint $table) {
            $table->dropIndex(['site_id', 'apartment_id']);
            $table->dropIndex(['site_id', 'period']);
            $table->dropIndex(['site_id', 'due_date']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['site_id', 'expense_date']);
            $table->dropIndex(['site_id', 'vendor_id']);
        });

        Schema::table('receipts', function (Blueprint $table) {
            $table->dropIndex(['site_id', 'paid_at']);
            $table->dropIndex(['site_id', 'apartment_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['site_id', 'paid_at']);
        });
    }
};
