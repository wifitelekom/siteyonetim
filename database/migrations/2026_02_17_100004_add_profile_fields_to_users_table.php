<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('address')->nullable()->after('phone');
            $table->date('birth_date')->nullable()->after('address');
            $table->string('occupation')->nullable()->after('birth_date');
            $table->string('education')->nullable()->after('occupation');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['address', 'birth_date', 'occupation', 'education']);
        });
    }
};
