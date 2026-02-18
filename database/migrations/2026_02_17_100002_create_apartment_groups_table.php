<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('apartment_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->decimal('multiplier', 8, 2)->default(1);
            $table->timestamps();

            $table->index('site_id');
        });

        Schema::table('apartments', function (Blueprint $table) {
            $table->foreignId('apartment_group_id')->nullable()->after('site_id')
                ->constrained('apartment_groups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('apartment_group_id');
        });
        Schema::dropIfExists('apartment_groups');
    }
};
