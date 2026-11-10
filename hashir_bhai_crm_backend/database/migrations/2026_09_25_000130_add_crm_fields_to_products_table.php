<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('type', 20)->default('product')->after('name');
            $table->string('brand')->nullable()->after('sku');
            $table->string('unit', 20)->default('pcs')->after('brand');
            $table->decimal('cost', 12, 2)->default(0)->after('old_price');
            $table->decimal('tax_rate', 5, 2)->default(0)->after('cost');
            $table->decimal('min_order_qty', 10, 2)->default(1)->after('tax_rate');
            $table->text('bot_description')->nullable()->after('description');
            $table->string('warranty')->nullable()->after('bot_description');
            $table->json('specs')->nullable()->after('warranty');
            $table->json('images')->nullable()->after('specs');
            $table->boolean('is_active')->default(true)->after('is_featured');
            $table->boolean('is_shareable_on_whatsapp')->default(true)->after('is_active');
            $table->boolean('show_to_bot')->default(true)->after('is_shareable_on_whatsapp');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'type', 'brand', 'unit', 'cost', 'tax_rate', 'min_order_qty', 'bot_description',
                'warranty', 'specs', 'images', 'is_active', 'is_shareable_on_whatsapp', 'show_to_bot',
            ]);
        });
    }
};
