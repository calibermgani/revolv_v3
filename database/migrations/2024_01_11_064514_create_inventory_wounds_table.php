<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryWoundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_wounds', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->nullable();
            $table->string('doctor')->nullable();
            $table->string('insurance_carrier')->nullable();
            $table->string('insurance_group')->nullable();
            $table->string('patient_id')->nullable();
            $table->string('patient_name')->nullable();
            $table->date('dob')->nullable();
            $table->date('dos')->nullable();
            $table->date('doe')->nullable();
            $table->string('department')->nullable();
            $table->string('facility')->nullable();
            $table->string('company')->nullable();
            $table->string('pos')->nullable();
            $table->string('coders_em_cpt')->nullable();
            $table->string('coders_em_icd_10')->nullable();
            $table->string('coders_procedure_cpt')->nullable();
            $table->string('coders_procedure_icd_10')->nullable();
            $table->string('billers_audit_cpt_comments')->nullable();
            $table->string('billers_audit_icd')->nullable();
            $table->string('doctors_mr_cpt')->nullable();
            $table->string('em_dx')->nullable();
            $table->string('severity_of_diagnosis')->nullable();
            $table->string('amount_and_or_complexity_of_data')->nullable();
            $table->string('risk_of_complications_and_or_morbidity')->nullable();
            $table->string('rationale')->nullable();
            $table->string('visit_status')->nullable();
            $table->string('visit_desc')->nullable();
            $table->string('cpt')->nullable();
            $table->string('units')->nullable();
            $table->string('modifier')->nullable();
            $table->string('diagnoses')->nullable();
            $table->string('coder_comment')->nullable();
            $table->date('inventory_date')->nullable();
            $table->string('CE_emp_id')->nullable();
            $table->string('QA_emp_id')->nullable();
            $table->enum('status',['CE_Inprocess','CE_Pending','CE_Completed','CE_Hold','QA_Inprocess','QA_Pending','QA_Completed','QA_Hold','Revoke'])->default('CE_Inprocess');
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
        Schema::dropIfExists('inventory_wounds');
    }
}
