<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kitchen_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->string('order_no', 40)->unique();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('whatsapp_contact_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone', 50)->nullable();
            $table->string('delivery_address')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->string('status', 30)->default('pending');
            $table->string('payment_method', 20)->nullable();
            $table->string('payment_status', 20)->default('unpaid');
            $table->string('payment_token', 64)->nullable()->index();
            $table->string('payment_reference')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('preparing_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->string('source', 30)->default('whatsapp_bot');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('kitchen_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->foreignId('kitchen_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('kitchen_orders', function (Blueprint $table) {
                $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            });

            Schema::table('kitchen_order_items', function (Blueprint $table) {
                $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kitchen_order_items');
        Schema::dropIfExists('kitchen_orders');
    }
};
