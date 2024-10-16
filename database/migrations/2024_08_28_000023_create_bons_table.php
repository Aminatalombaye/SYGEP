<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBonsTable extends Migration
{
    public function up()
    {
        Schema::create('bons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date_emission');
            $table->string('organisation')->unique();
            $table->string('reference_commande');
            $table->string('nom_destinataire');
            $table->string('bon')->nullable();
            $table->date('date_livraison')->nullable();
            $table->timestamps();
        });
    }
}
