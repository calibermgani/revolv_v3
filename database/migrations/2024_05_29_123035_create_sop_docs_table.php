<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSopDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sop_docs', function (Blueprint $table) {
            $table->id();
            $table->text('project_id')->nullable();
            $table->text('sub_project_id')->nullable();
            $table->text('sop_doc')->nullable();
            $table->text('sop_path')->nullable();
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
        Schema::dropIfExists('sop_docs');
    }
}
