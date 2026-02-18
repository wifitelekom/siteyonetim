<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dateTime('archived_at')->nullable()->after('remember_token');
            $table->index(['site_id', 'archived_at']);
        });

        Schema::table('charges', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['site_id', 'archived_at']);
            $table->dropColumn('archived_at');
        });

        Schema::table('charges', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable(false)->change();
        });
    }
};
