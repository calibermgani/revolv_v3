<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCallerChartsWorkLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('caller_charts_work_logs', function (Blueprint $table) {
            $table->string('work_time')->after('end_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('caller_charts_work_logs', function (Blueprint $table) {
            $table->dropColumn('work_time');
        });
    }
}
