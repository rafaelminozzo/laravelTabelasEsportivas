<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('confrontos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competicao_id')->constrained('competicaos')->onDelete('cascade');
            $table->string('fase'); // Ex: 'oitavas', 'quartas', 'semifinal', 'final'
            $table->string('jogador1')->nullable();
            $table->string('jogador2')->nullable();
            $table->integer('sets_jogador1')->nullable();
            $table->integer('sets_jogador2')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('confrontos');
    }
};
