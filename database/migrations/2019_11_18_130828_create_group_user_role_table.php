<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupUserRoleTable extends Migration
{
  /**
  * Run the migrations.
  *
  * @return void
  */
  public function up()
  {
    // Schema::create('menus', function (Blueprint $table) {
    //     $table->bigIncrements('id');
    //     $table->string('text', 25);
    //     $table->string('url', 25);
    //     $table->string('icon', 25)->nullable();
    //     $table->unsignedBigInteger('parent_id')->nullable();
    //     $table->timestamps();

    //     $table->foreign('parent_id')->references('id')->on('roles')->onDelete('restrict');
    // });

    Schema::create('roles', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('name', 25);
      $table->string('display_name', 25);
      $table->text('description')->nullable();
      $table->unsignedBigInteger('parent_id')->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestamps();

      $table->foreign('parent_id')->references('id')->on('roles')->onDelete('restrict');
    });

    Schema::create('role_menus', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('menu_key');
      $table->unsignedBigInteger('role_id');
      $table->timestamps();

      $table->foreign('role_id')->references('id')->on('roles')->onDelete('restrict');
    });

    Schema::table('users', function (Blueprint $table) {
      $table->unsignedBigInteger('role_id')->nullable();

      $table->foreign('role_id')->references('id')->on('roles')->onDelete('restrict');
    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    Schema::disableForeignKeyConstraints();
    Schema::table('users', function (Blueprint $table) {
      $table->dropForeign('users_role_id_foreign');
      $table->dropColumn(['role_id']);
    });

    Schema::dropIfExists('role_menus');
    Schema::dropIfExists('roles');
    Schema::enableForeignKeyConstraints();
  }
}
