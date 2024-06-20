<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_menus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('menu_id')->nullable();
            $table->string('sub_menu_name')->nullable();
            $table->string('sub_menu_name_url')->nullable();
            $table->enum('status',['Active','Inactive'])->default('Active');
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
            $table->text('sub_menu_name_icon')->nullable();
            $table->integer('sub_menu_order')->nullable();
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
        Schema::dropIfExists('sub_menus');
    }
}
