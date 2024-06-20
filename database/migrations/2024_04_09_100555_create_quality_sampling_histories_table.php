<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQualitySamplingHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quality_sampling_histories', function (Blueprint $table) {
            $table->id();
            $table->string('project_id')->nullable();
            $table->string('sub_project_id')->nullable();
            $table->string('coder_emp_id')->nullable();
            $table->string('qa_emp_id')->nullable();
            $table->string('qa_percentage')->nullable();
            $table->string('claim_priority')->nullable();
            $table->string('added_by')->nullable();
            $table->string('quality_sampling_id')->nullable();
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
        Schema::dropIfExists('quality_sampling_histories');
    }
}
