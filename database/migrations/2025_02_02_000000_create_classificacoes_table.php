<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassificacoesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('classificacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competicao_id')->constrained('competicaos')->onDelete('cascade');
            $table->string('jogador'); // Nome do jogador
            $table->integer('posicao'); // Posição final na competição
            $table->integer('pontos'); // Pontuação final
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classificacoes');
    }
}