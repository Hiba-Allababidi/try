<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeepersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keepers', function (Blueprint $table) {
            $table->id();
            $table->string('keeper_name');
            $table->string('keeper_email')->unique()->where('is_active',true);
            $table->string('keeper_phone_number')->nullable();
            $table->string('keeper_password');
            $table->boolean('is-active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keepers');
    }
}
