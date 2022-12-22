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
        if (!Schema::hasTable('shopify_products')) {

            Schema::create('shopify_products', function (Blueprint $table) {
                $table->id();
                $table->integer('store_id')->nullable(false);
                $table->string('title')->nullable(true);
                $table->string('body_html')->nullable(true);
                $table->string('vendor')->nullable(true);
                $table->string('product_type')->nullable(true);
                $table->string('shopify_created_at')->nullable(true);
                $table->string('handle')->nullable(true);
                $table->string('shopify_updated_at')->nullable(true);
                $table->string('shopify_published_at')->nullable(true);
                $table->string('template_suffix')->nullable(true);
                $table->string('status')->nullable(true);
                $table->string('published_scope')->nullable(true);
                $table->string('tag')->nullable(true);
                $table->string('admin_graphql_api_id')->nullable(true);
                $table->timestamps();
                $table->unique(['store_id','handle']);
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
        Schema::dropIfExists('shopify_products');
    }
};
