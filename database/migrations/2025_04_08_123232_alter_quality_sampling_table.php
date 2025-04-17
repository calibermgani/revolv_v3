<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterQualitySamplingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quality_samplings', function (Blueprint $table) {
            $table->text('qa_sample_column_data_type')->after('qa_sample_column_value')->nullable();
            $table->text('qa_sample_column_condition')->after('qa_sample_column_data_type')->nullable();
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
            if (Schema::hasColumn('quality_samplings', 'qa_sample_column_data_type')) {
                $table->dropColumn('qa_sample_column_data_type');
            }
    
            if (Schema::hasColumn('quality_samplings', 'qa_sample_column_condition')) {
                $table->dropColumn('qa_sample_column_condition');
            }
        });
    }
}
