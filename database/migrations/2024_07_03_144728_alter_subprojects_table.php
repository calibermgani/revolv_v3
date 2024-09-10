<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterSubprojectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subprojects', function (Blueprint $table) {
            $table->string('aims_sub_project_name')->nullable()->after('sub_project_id');
       });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subprojects', function (Blueprint $table) {
            $table->dropColumn('aims_sub_project_name');
        });
    }
}
