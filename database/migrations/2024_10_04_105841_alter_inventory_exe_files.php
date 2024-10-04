<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterInventoryExeFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_exe_files', function (Blueprint $table) {
            $table->string('inventory_count')->nullable()->after('exe_date');
       });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_exe_files', function (Blueprint $table) {
            $table->dropColumn('inventory_count');
        });
    }
}
