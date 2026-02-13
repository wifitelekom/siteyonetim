<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicate charges before adding constraint (keep the oldest)
        DB::statement('
            DELETE c1 FROM charges c1
            INNER JOIN charges c2
            ON c1.site_id = c2.site_id
                AND c1.apartment_id = c2.apartment_id
                AND c1.period = c2.period
                AND c1.account_id = c2.account_id
                AND c1.id > c2.id
        ');

        Schema::table('charges', function (Blueprint $table) {
            $table->unique(
                ['site_id', 'apartment_id', 'period', 'account_id'],
                'charges_site_apt_period_account_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('charges', function (Blueprint $table) {
            $table->dropUnique('charges_site_apt_period_account_unique');
        });
    }
};
