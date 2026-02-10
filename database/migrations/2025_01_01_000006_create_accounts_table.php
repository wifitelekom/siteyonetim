<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('type'); // income, expense, asset, liability
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['site_id', 'code']);
        });
    }
    public function down(): void { Schema::dropIfExists('accounts'); }
};
