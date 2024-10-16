<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInfrastructureProjectPivotTable extends Migration
{
    public function up()
    {
        Schema::create('infrastructure_project', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id', 'project_id_fk_9996231')->references('id')->on('projects')->onDelete('cascade');
            $table->unsignedBigInteger('infrastructure_id');
            $table->foreign('infrastructure_id', 'infrastructure_id_fk_9996231')->references('id')->on('infrastructures')->onDelete('cascade');
        });
    }
}
