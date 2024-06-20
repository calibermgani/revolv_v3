<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMainMenuPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
             Schema::create('main_menu_permission', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('user_id')->nullable();;
                $table->string('parent_id')->nullable();;
                $table->integer('menu_permission_given_by')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('main_menu_permissions');
    }
}
