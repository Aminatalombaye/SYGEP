<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssignmentAttributionPivotTable extends Migration
{
    public function up()
    {
        Schema::create('assignment_attribution', function (Blueprint $table) {
            $table->unsignedBigInteger('assignment_id');
            $table->foreign('assignment_id', 'assignment_id_fk_10016939')->references('id')->on('assignments')->onDelete('cascade');
            $table->unsignedBigInteger('attribution_id');
            $table->foreign('attribution_id', 'attribution_id_fk_10016939')->references('id')->on('attributions')->onDelete('cascade');
        });
    }
}
