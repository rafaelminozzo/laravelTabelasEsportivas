<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJogosTable extends Migration
{
    public function up()
    {
        Schema::create('jogos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competicao_id')->constrained('competicaos')->onDelete('cascade');
            $table->string('jogador1');
            $table->string('jogador2');
            // Usaremos estes campos para armazenar os sets ganhos por cada jogador
            $table->integer('sets_jogador1')->nullable();
            $table->integer('sets_jogador2')->nullable();
            // Campo para identificar o grupo (caso seja Copa)
            $table->string('grupo')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jogos');
    }
}
