<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventairesTable extends Migration
{
    public function up()
    {
        Schema::create('inventaires', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('in')->nullable();
            $table->string('out')->nullable();
            $table->string('balance')->nullable();
            $table->string('reference')->nullable();
            $table->string('nom')->nullable();
            $table->timestamps();
        });
    }
}
