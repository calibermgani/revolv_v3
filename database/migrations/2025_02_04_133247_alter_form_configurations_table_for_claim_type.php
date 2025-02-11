<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFormConfigurationsTableForClaimType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_configurations', function (Blueprint $table) {
            $table->integer('claim_type')->after('project_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('form_configurations', function (Blueprint $table) {
            $table->dropColumn('claim_type');
        });
    }
}
