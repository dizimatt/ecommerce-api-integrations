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
        if (!Schema::hasTable('bigcommerce_stores')) {

            Schema::create('bigcommerce_stores', function (Blueprint $table) {
                $table->id();
                $table->string('domain');
                $table->string('api_url');
                $table->string('graphql_url');
                $table->string('name');
                $table->string('timezone')->default('UTC');
                $table->string('currency');
                $table->string('contact_emails');
                $table->string('api_token');
                $table->string('graphql_token', 500);
                $table->string('access_token');
                $table->timestamps();
                $table->string('webhook_signature');
                $table->index('domain');
                $table->index('name');
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
        Schema::dropIfExists('bigcommerce_stores');
    }
};
