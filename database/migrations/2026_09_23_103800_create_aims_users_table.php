<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAimsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aims_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('aims_user_id')->nullable();
            $table->string('emp_id', 50)->nullable()->unique();
            $table->string('user_name', 255)->nullable();
            $table->string('status', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['emp_id', 'status', 'deleted_at'], 'aims_users_emp_status_deleted_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aims_users');
    }
}
