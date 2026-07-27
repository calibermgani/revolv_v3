<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAimsProjectResourceAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aims_project_resource_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->text('sub_project')->nullable();
            $table->string('assigned_people', 255)->nullable();
            $table->string('department', 255)->nullable();
            $table->string('department_name', 255)->nullable();
            $table->string('shift_name', 255)->nullable();
            $table->string('current_designation', 255)->nullable();
            $table->string('status', 255)->nullable();
            $table->string('resource_type', 50)->nullable();
            $table->string('percentage', 255)->nullable();
            $table->string('emp_id', 255)->nullable();
            $table->string('user_status', 255)->nullable();
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
        Schema::dropIfExists('aims_project_resource_allocations');
    }
}
