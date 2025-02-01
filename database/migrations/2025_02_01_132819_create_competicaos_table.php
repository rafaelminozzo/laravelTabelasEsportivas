<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompeticaosTable extends Migration
{
    public function up()
    {
        Schema::create('competicaos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('formato'); // Ex: 'copa' ou 'mata-mata'
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('competicaos');
    }
}
