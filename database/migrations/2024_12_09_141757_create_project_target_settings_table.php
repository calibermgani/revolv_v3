<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectTargetSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_target_settings', function (Blueprint $table) {
            $table->id();
            $table->string('project_id')->nullable();
            $table->string('sub_project_id')->nullable();
            $table->string('activity')->nullable();
            $table->string('sub_activity')->nullable();
            $table->string('target_per_hour')->nullable();
            $table->string('target_per_day')->nullable();
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
        Schema::dropIfExists('project_target_settings');
    }
}
