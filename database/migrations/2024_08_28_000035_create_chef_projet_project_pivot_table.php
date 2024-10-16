<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChefProjetProjectPivotTable extends Migration
{
    public function up()
    {
        Schema::create('chef_projet_project', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id', 'project_id_fk_10016902')->references('id')->on('projects')->onDelete('cascade');
            $table->unsignedBigInteger('chef_projet_id');
            $table->foreign('chef_projet_id', 'chef_projet_id_fk_10016902')->references('id')->on('chef_projets')->onDelete('cascade');
        });
    }
}
