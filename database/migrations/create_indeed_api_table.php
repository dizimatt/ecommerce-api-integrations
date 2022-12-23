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
        if (!Schema::hasTable('indeed_api')) {

            Schema::create('indeed_api', function (Blueprint $table) {
                $table->id();
                $table->string('api_url');
                $table->string('name');
                $table->text('access_token');
                $table->timestamps();
                $table->index('name');
                $table->unique(['name']);
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
        Schema::dropIfExists('indeed_api');
    }
};
