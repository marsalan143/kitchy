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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('order_number');
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->onDelete('set null');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->date('event_date');
            $table->time('food_preparation_start_time')->nullable();
            $table->time('food_delivery_time')->nullable();
            $table->time('event_start_time')->nullable();
            $table->enum('delivery_status', ['not_delivered', 'partially_delivered', 'fully_delivered'])->default('not_delivered');
            $table->text('delivery_notes')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->boolean('gst_enabled')->default(false);
            $table->decimal('gst_percentage', 5, 2)->default(0);
            $table->decimal('gst_amount', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique('order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
