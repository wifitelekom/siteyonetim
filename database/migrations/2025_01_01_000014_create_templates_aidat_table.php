<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('templates_aidat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('amount', 14, 2);
            $table->unsignedTinyInteger('due_day');
            $table->foreignId('account_id')->constrained();
            $table->string('scope')->default('all'); // all, selected
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('templates_aidat'); }
};
