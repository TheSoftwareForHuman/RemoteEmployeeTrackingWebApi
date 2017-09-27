<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');
            $table->string('name');
            $table->string('login')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('data', function (Blueprint $table) {
            $table->increments('id');
            $table->string('login');
            $table->foreign('login')->references('login')->on('users');
            $table->string('token');
            $table->string('filename');
            $table->binary('data');
            $table->rememberToken();
            $table->timestamps();
        });

        $debug = config('app.debug');

        if ($debug) {

            DB::table('users')->insert(
                [
                'name' => 'Konstantin Eletskiy',
                'login' => 'konstantin.eletskiy@gmail.com', 
                'password' => bcrypt('secret')
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data');
        Schema::dropIfExists('users');
    }
}
