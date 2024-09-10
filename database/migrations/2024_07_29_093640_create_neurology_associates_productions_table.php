<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNeurologyAssociatesProductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('neurology_associates_productions', function (Blueprint $table) {
            $table->id();
            $table->string('patient_id')->nullable();
            $table->date('date_of_service')->nullable();
            $table->string('patient_name')->nullable();
            $table->text('payer')->nullable();
            $table->string('provider')->nullable();
            $table->text('na_cpt')->nullable();
            $table->text('annex_cpt')->nullable();
            $table->text('cpt_changes')->nullable();
            $table->text('na_icd')->nullable();
            $table->text('annex_icd')->nullable();
            $table->text('icd_changes')->nullable();
            $table->text('na_mod')->nullable();
            $table->text('annex_mod')->nullable();
            $table->text('mod_changes')->nullable();
            $table->text('coding_comments')->nullable();
            $table->date('production_date')->nullable();
            $table->string('status')->nullable();
            $table->string('chart_audited')->nullable();
            $table->string('no_Of_errors_captured')->nullable();
            $table->string('error_category')->nullable();
            $table->string('error_type')->nullable();
            $table->string('auditor_comments')->nullable();
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
        Schema::dropIfExists('neurology_associates_productions');
    }
}
