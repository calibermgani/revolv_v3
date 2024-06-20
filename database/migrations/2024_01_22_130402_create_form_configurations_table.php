<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_configurations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('project_id')->nullable();
            $table->string('sub_project_id')->nullable();
            $table->string('label_name')->nullable();
            $table->string('input_type')->nullable();
            $table->string('options_name')->nullable();
            $table->string('field_type')->nullable();
            $table->string('field_type_1')->nullable();
            $table->string('field_type_2')->nullable();
            $table->string('field_type_3')->nullable();
            $table->string('added_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('form_configurations');
    }
}
