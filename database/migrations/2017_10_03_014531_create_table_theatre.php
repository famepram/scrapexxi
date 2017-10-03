<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTheatre extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        Schema::create('theatre', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ori_id');
            $table->integer('city_id');
            $table->string('name');
            $table->string('code');
            $table->string('slug');
            $table->string('address');
            $table->string('phone');
            $table->string('cnpx_link');
            $table->double('lat', 15, 8);
            $table->double('lng', 15, 8);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('theatre');
    }
}
