<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('apartment_user', function (Blueprint $table) {
            $table->string('family_role')->nullable()->after('relation_type');
        });
    }

    public function down(): void
    {
        Schema::table('apartment_user', function (Blueprint $table) {
            $table->dropColumn('family_role');
        });
    }
};
