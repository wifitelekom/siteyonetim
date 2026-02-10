<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('apartment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained();
            $table->string('charge_type'); // aidat, other
            $table->string('period', 7); // YYYY-MM
            $table->date('due_date');
            $table->decimal('amount', 14, 2);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('charges'); }
};
