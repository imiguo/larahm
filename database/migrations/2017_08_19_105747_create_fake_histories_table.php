<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFakeHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fake_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->double('amount', 10, 5)->default(0.00000);
            $table->tinyInteger('payment');
            $table->tinyInteger('type');
            $table->timestamp('time')->nullable();
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
        Schema::dropIfExists('fake_histories');
    }
}
