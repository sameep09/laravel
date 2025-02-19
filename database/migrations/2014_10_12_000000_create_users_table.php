<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('post');
            $table->string('email')->unique();
            $table->enum('user_type', [1, 2, 3, 4])->default('4')->comment('1 => Super Admin, 2 => Admin, 3 => Officer, 4 => User');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('ins')->default('0')->comment('0 => disable, 1 => enable');
            $table->boolean('edit')->default('0')->comment('0 => disable, 1 => enable');
            $table->boolean('delete')->default('0')->comment('0 => disable, 1 => enable');
            $table->boolean('setup')->default('0')->comment('0 => disable, 1 => enable');
            $table->boolean('data_entry')->default('0')->comment('0 => disable, 1 => enable');
            $table->boolean('report')->default('0')->comment('0 => disable, 1 => enable');
            $table->boolean('status')->default('1')->comment('0 => deactive, 1 => active');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
