<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterColumnNameAndColumnValueToQualitySamplings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quality_samplings', function (Blueprint $table) {
            $table->text('qa_sample_column_name')->after('claim_priority')->nullable();
            $table->text('qa_sample_column_value')->after('qa_sample_column_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quality_samplings', function (Blueprint $table) {
            $table->dropColumn('qa_sample_column_name');
            $table->dropColumn('qa_sample_column_value');
        });
    }
}
