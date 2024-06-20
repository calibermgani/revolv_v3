<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMomParentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mom_parents', function (Blueprint $table) {
            $table->id();
            $table->text('meeting_title')->nullable();
            $table->text('meeting_attendies')->nullable();
            $table->string('time_zone')->nullable();
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->string('meeting_date')->nullable();
            $table->string('start')->nullable();
            $table->string('end')->nullable();
            $table->date('eta')->nullable();
            $table->text('req_description')->nullable();
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
        Schema::dropIfExists('mom_parents');
    }
}
