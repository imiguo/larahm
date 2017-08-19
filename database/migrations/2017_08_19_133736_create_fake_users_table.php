<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFakeUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fake_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 30);
            $table->double('amount', 10, 5)->default(0.00000);
            $table->double('deposit', 10, 5)->default(0.00000);
            $table->double('withdraw', 10, 5)->default(0.00000);
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
        Schema::dropIfExists('fake_users');
    }
}
