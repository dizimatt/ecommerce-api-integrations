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
        Schema::create('konakart_accounts', function (Blueprint $table) {
            $table->id();
            $table->integer('store_id');
            $table->string('live_username');
            $table->string('live_password');
            $table->string('sandbox_username');
            $table->string('sandbox_password');
            $table->boolean('using_sandbox')->default(true);
            $table->string('sandbox_url');
            $table->string('live_url');
            $table->string('sandbox_token');
            $table->string('live_token');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('konakart_accounts');
    }
};
