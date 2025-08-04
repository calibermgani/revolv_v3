<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateARDenialCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('a_r_denial_codes', function (Blueprint $table) {
            $table->id();
            $table->string('project_id')->nullable();
            $table->string('sub_project_id')->nullable();
            $table->text('denial_code')->nullable();
            $table->text('code_description')->nullable();
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
        Schema::dropIfExists('a_r_denial_codes');
    }
}
