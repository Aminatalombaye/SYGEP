<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttributionsTable extends Migration
{
    public function up()
    {
        Schema::create('attributions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('details')->nullable();
            $table->string('nom')->nullable();
            $table->string('type_atribution')->nullable();
            $table->timestamps();
        });
    }
}
