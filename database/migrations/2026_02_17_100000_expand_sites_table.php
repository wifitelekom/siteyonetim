<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('city')->nullable()->after('address');
            $table->string('district')->nullable()->after('city');
            $table->string('zip_code')->nullable()->after('district');
            $table->string('tax_office')->nullable()->after('tax_no');
            $table->string('contact_person')->nullable()->after('tax_office');
            $table->string('contact_email')->nullable()->after('contact_person');
            $table->string('contact_phone')->nullable()->after('contact_email');
            $table->string('country')->default('Türkiye')->after('contact_phone');
            $table->string('language')->default('tr')->after('country');
            $table->string('timezone')->default('Europe/Istanbul')->after('language');
            $table->string('currency')->default('TRY')->after('timezone');
            $table->json('permission_settings')->nullable()->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn([
                'city', 'district', 'zip_code', 'tax_office',
                'contact_person', 'contact_email', 'contact_phone',
                'country', 'language', 'timezone', 'currency',
                'permission_settings',
            ]);
        });
    }
};
