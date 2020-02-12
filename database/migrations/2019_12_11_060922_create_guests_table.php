<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->increments('id');
			$table->bigInteger('group_id')->index();
			$table->string('first');
			$table->string('last');
			$table->string('phone');
			$table->string('email');
			$table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('zip');
			$table->string('arrivalMethod');
			$table->timestamp('arrivalTime');
			$table->string('departureMethod');
			$table->timestamp('departureTime');
			$table->json('details')->default('[]');
			$table->string('notes')->nullable();
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
        Schema::dropIfExists('guests');
    }
}
