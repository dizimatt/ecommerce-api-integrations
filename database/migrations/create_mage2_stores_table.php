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
        if (!Schema::hasTable('mage2_stores')) {

            Schema::create('mage2_stores', function (Blueprint $table) {
                $table->id();
                $table->string('hostname');
                $table->string('name');
                $table->string('access_token');
                $table->string('access_token_secret');
                $table->timestamps();
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
        Schema::dropIfExists('mage2_stores');
    }
};
