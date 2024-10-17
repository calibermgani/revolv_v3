<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQaClassCatScopesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qa_class_cat_scopes', function (Blueprint $table) {
            $table->id();
            $table->string('project_id')->nullable();
            $table->string('sub_project_id')->nullable();
            $table->string('status_code_id')->nullable();
            $table->text('sub_status_code_id')->nullable();
            $table->string('qa_classification')->nullable();
            $table->string('qa_category')->nullable();
            $table->string('qa_scope')->nullable();
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
        Schema::dropIfExists('qa_class_cat_scopes');
    }
}
