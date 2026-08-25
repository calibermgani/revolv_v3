<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlertConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_configurations', function (Blueprint $table) {
            $table->id();  
            $table->integer('project_id');
            $table->integer('sub_project_id');

            $table->string('project_column', 255);

            $table->string('condition', 30);

            $table->text('value');

            /*
             * Multiple users:
             * AM5125,AM5219,AM4562
             */
            $table->text('emp_id');

            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();

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
        Schema::dropIfExists('alert_configurations');
    }
}
