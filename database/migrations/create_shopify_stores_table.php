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
        if (!Schema::hasTable('shopify_stores')) {

            Schema::create('shopify_stores', function (Blueprint $table) {
                $table->id();
                $table->string('domain');
                $table->string('hostname');
                $table->string('name');
                $table->string('timezone')->default('UTC');
                $table->string('currency');
                $table->string('contact_emails');
                $table->string('access_token');
                $table->string('scope');
                $table->timestamps();
                $table->bigInteger('orders_since_id');
                $table->string('webhook_signature');
                $table->timestamp('nonce_created_at')->nullable();
                $table->string('nonce');
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
        Schema::dropIfExists('shopify_stores');
    }
};
