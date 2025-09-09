<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBackEndUploadTemplateExeFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('back_end_upload_template_exe_files', function (Blueprint $table) {
            $table->id();
            $table->string('project_id')->nullable();
            $table->string('sub_project_id')->nullable();
            $table->string('file_name')->nullable();
            $table->dateTime('exe_date')->nullable();
            $table->string('upload_status')->nullable()->enum('Yes','No')->default('Yes');
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
        Schema::dropIfExists('back_end_upload_template_exe_files');
    }
}
