<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectReasonTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_reason_types', function (Blueprint $table) {
            $table->id();
            $table->string('reason_type')->nullable();
            $table->enum('reason_access',[1,2,3])->default(3) ->comment('1 - AR, 2 - QA, 3 - Both');;
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
        Schema::dropIfExists('project_reason_types');
    }
}
