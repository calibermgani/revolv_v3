<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateARSubStatusCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('a_r_sub_status_codes', function (Blueprint $table) {
            $table->id();
            $table->string('project_id')->nullable();
            $table->string('sub_project_id')->nullable();
            $table->text('sub_status_code')->nullable();
            $table->text('sub_status_code_description')->nullable();
            $table->enum('status',['Active','Inactive'])->default('Active');
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
        Schema::dropIfExists('a_r_sub_status_codes');
    }
}
