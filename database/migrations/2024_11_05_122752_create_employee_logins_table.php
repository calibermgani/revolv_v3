<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeLoginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_logins', function (Blueprint $table) {
            $table->id();
            $table->time('in_time')->nullable();
			$table->time('out_time')->nullable();
			$table->integer('user_id')->nullable();
			$table->time('duration')->nullable();
            $table->time('break_hrs')->nullable();
            $table->date('login_date')->nullable();
            $table->date('logout_date')->nullable();
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
        Schema::dropIfExists('employee_logins');
    }
}
