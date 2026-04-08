<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('quotation_number');
            $table->integer('revision_number')->default(1);
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->date('event_date');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->boolean('gst_enabled')->default(false);
            $table->decimal('gst_percentage', 5, 2)->default(0);
            $table->decimal('gst_amount', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->enum('status', ['draft', 'sent', 'approved', 'rejected'])->default('draft');
            $table->date('expiry_date')->nullable();
            $table->foreignId('parent_quotation_id')->nullable()->constrained('quotations')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['quotation_number', 'revision_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
