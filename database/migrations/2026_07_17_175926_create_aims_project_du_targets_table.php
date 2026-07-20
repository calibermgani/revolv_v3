<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAimsProjectDuTargetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aims_project_du_targets', function (Blueprint $table) {
            $table->id();

            $table->text('project');
            $table->string('scope_name', 255)->nullable();

            /*
             * These are strings because the source JSON columns are currently
             * extracted as VARCHAR(255). This prevents import failure if the
             * source contains text, percentages, decimals, or blank values.
             */
            $table->string('billable_fte', 255)->nullable();
            $table->string('actual_target', 255)->nullable();

            $table->string('du', 255)->nullable();

            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // $table->index('project');
            // $table->index('scope_name');
            // $table->index('du');
            // $table->index(['project', 'scope_name', 'du']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aims_project_du_targets');
    }
}
