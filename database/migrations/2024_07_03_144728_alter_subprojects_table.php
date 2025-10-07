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
            $table->text('new_sub_project_name')->nullable()->after('sub_project_name');
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
            $table->dropColumn('new_sub_project_name');
        });
    }
}
