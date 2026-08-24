<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('shop_name'); $table->string('slug')->unique(); $table->text('description')->nullable();
            $table->string('logo')->nullable(); $table->string('phone')->nullable(); $table->string('email')->nullable();
            $table->text('address')->nullable(); $table->string('status')->default('active'); $table->timestamps();
        });
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('slug')->unique(); $table->text('description')->nullable();
            $table->string('image')->nullable(); $table->string('status')->default('active'); $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id(); $table->foreignId('vendor_id')->constrained()->cascadeOnDelete(); $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name'); $table->string('slug')->unique(); $table->string('sku')->unique(); $table->text('description')->nullable();
            $table->decimal('price', 12, 2); $table->decimal('discount_price', 12, 2)->nullable(); $table->unsignedInteger('stock')->default(0);
            $table->string('image')->nullable(); $table->string('status')->default('active'); $table->boolean('featured')->default(false); $table->timestamps();
        });
        Schema::create('addresses', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('label')->default('Home'); $table->text('address'); $table->string('city'); $table->string('phone')->nullable(); $table->boolean('is_default')->default(false); $table->timestamps(); });
        Schema::create('carts', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete(); $table->string('coupon_code')->nullable(); $table->timestamps(); });
        Schema::create('cart_items', function (Blueprint $table) { $table->id(); $table->foreignId('cart_id')->constrained()->cascadeOnDelete(); $table->foreignId('product_id')->constrained()->cascadeOnDelete(); $table->unsignedInteger('quantity'); $table->decimal('unit_price', 12, 2); $table->timestamps(); $table->unique(['cart_id', 'product_id']); });
        Schema::create('wishlists', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->foreignId('product_id')->constrained()->cascadeOnDelete(); $table->timestamps(); $table->unique(['user_id', 'product_id']); });
        Schema::create('orders', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->decimal('subtotal', 12, 2); $table->decimal('discount', 12, 2)->default(0); $table->decimal('tax', 12, 2)->default(0); $table->decimal('shipping', 12, 2)->default(0); $table->decimal('grand_total', 12, 2); $table->string('status')->default('pending')->index(); $table->text('shipping_address'); $table->timestamps(); });
        Schema::create('order_items', function (Blueprint $table) { $table->id(); $table->foreignId('order_id')->constrained()->cascadeOnDelete(); $table->foreignId('product_id')->constrained(); $table->foreignId('vendor_id')->constrained(); $table->unsignedInteger('quantity'); $table->decimal('unit_price', 12, 2); $table->timestamps(); });
        Schema::create('payments', function (Blueprint $table) { $table->id(); $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete(); $table->string('method'); $table->string('status')->default('pending'); $table->decimal('amount', 12, 2); $table->timestamps(); });
        Schema::create('reviews', function (Blueprint $table) { $table->id(); $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete(); $table->foreignId('product_id')->constrained()->cascadeOnDelete(); $table->foreignId('order_id')->constrained()->cascadeOnDelete(); $table->unsignedTinyInteger('rating'); $table->text('comment')->nullable(); $table->string('status')->default('approved'); $table->timestamps(); $table->unique(['customer_id', 'product_id', 'order_id']); });
        Schema::create('coupons', function (Blueprint $table) { $table->id(); $table->string('code')->unique(); $table->string('type'); $table->decimal('value', 12, 2); $table->decimal('minimum_order_amount', 12, 2)->default(0); $table->decimal('maximum_discount', 12, 2)->nullable(); $table->timestamp('start_date')->nullable(); $table->timestamp('end_date')->nullable(); $table->unsignedInteger('usage_limit')->nullable(); $table->unsignedInteger('used_count')->default(0); $table->boolean('status')->default(true); $table->timestamps(); });
        Schema::create('product_images', function (Blueprint $table) { $table->id(); $table->foreignId('product_id')->constrained()->cascadeOnDelete(); $table->string('path'); $table->timestamps(); });
        Schema::create('inventory_transactions', function (Blueprint $table) { $table->id(); $table->foreignId('product_id')->constrained()->cascadeOnDelete(); $table->integer('quantity'); $table->string('type'); $table->nullableMorphs('reference'); $table->timestamps(); });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions'); Schema::dropIfExists('product_images'); Schema::dropIfExists('coupons'); Schema::dropIfExists('reviews'); Schema::dropIfExists('payments'); Schema::dropIfExists('order_items'); Schema::dropIfExists('orders'); Schema::dropIfExists('wishlists'); Schema::dropIfExists('cart_items'); Schema::dropIfExists('carts'); Schema::dropIfExists('addresses'); Schema::dropIfExists('products'); Schema::dropIfExists('categories'); Schema::dropIfExists('vendors');
    }
};