<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMomChildrenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mom_children', function (Blueprint $table) {
            $table->id();
            $table->string('mom_id')->nullable();
            $table->text('topics')->nullable();
            $table->text('topic_description')->nullable();
            $table->text('action_item')->nullable();
            $table->text('responsible_party')->nullable();
            $table->date('topic_eta')->nullable();
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
        Schema::dropIfExists('mom_children');
    }
}
