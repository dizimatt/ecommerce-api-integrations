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
        Schema::create('dolibarr_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('sandbox_url');
            $table->string('sandbox_login');
            $table->string('sandbox_password');
            $table->string('sandbox_token',500);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('dolibarr_accounts');
    }
};
