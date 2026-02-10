<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('templates_expense', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('amount', 14, 2);
            $table->unsignedTinyInteger('due_day');
            $table->string('period'); // monthly, quarterly, yearly
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_id')->constrained();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('templates_expense'); }
};
