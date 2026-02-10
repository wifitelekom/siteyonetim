<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('receipt_no');
            $table->foreignId('apartment_id')->constrained();
            $table->foreignId('cash_account_id')->constrained();
            $table->date('paid_at');
            $table->string('method'); // cash, bank
            $table->decimal('total_amount', 14, 2);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->unique(['site_id', 'receipt_no']);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('receipts'); }
};
