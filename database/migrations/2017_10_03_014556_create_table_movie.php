<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMovie extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        Schema::create('movie', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ori_id');
            $table->string('title');
            $table->string('synopsis');
            $table->string('mtix_code');
            $table->string('slug');
            $table->string('category');
            $table->string('producer');
            $table->string('director');
            $table->string('author');
            $table->string('production_house');
            $table->string('casts');
            $table->string('cover');
            $table->string('trailer_link');
            $table->integer('rate_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('movie');
    }
}
