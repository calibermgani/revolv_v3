<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterInventoryExeFileAddColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_exe_files', function (Blueprint $table) {
            $table->string('upload_status')->nullable()->default('auto')->after('inventory_count');
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
            $table->dropColumn('upload_status');
        });
    }
}
