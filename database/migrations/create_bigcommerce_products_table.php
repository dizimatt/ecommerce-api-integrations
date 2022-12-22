<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('bigcommerce_products')) {

            Schema::create('bigcommerce_products', function (Blueprint $table) {
                $table->id();
                $table->integer('store_id')->nullable(false);
                $table->string('name')->nullable(true);
                $table->string('type')->nullable(true);
                $table->string('sku')->nullable(true);
                $table->string('description')->nullable(true);
                $table->decimal('weight',8,2)->nullable(true);
                $table->decimal('width',8,2)->nullable(true);
                $table->decimal('depth',8,2)->nullable(true);
                $table->decimal('height',8,2)->nullable(true);
                $table->decimal('price',8,2)->nullable(true);
                $table->decimal('cost_price',8,2)->nullable(true);
                $table->decimal('retail_price',8,2)->nullable(true);
                $table->decimal('sale_price',8,2)->nullable(true);
                $table->decimal('map_price',8,2)->nullable(true);
                $table->integer('tax_class_id')->nullable(true);
                $table->string('product_tax_code')->nullable(true);
                $table->decimal('calculated_price',8,2)->nullable(true);
                $table->integer('brand_id')->nullable(true);
                $table->integer('inventory_level')->nullable(true);
                $table->integer('inventory_warning_level')->nullable(true);
                $table->string('inventory_tracking')->nullable(true);
                $table->integer('total_sold')->nullable(true);
                $table->integer('fixed_cost_shipping_price')->nullable(true);
                $table->boolean('is_free_shipping')->nullable(true);
                $table->boolean('is_visible')->nullable(true);
                $table->boolean('is_featured')->nullable(true);
                $table->string('date_created')->nullable(true);
                $table->string('date_modified')->nullable(true);
                $table->timestamps();
                $table->unique(['store_id','sku']);
                $table->unique(['store_id','name']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bigcommerce_products');
    }
};
