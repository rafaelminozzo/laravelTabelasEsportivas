<?php

namespace Database\Seeders;

use App\Models\Jogador;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JogadorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpa os jogadores existentes (opcional)
        Jogador::truncate();

        // Adiciona jogadores de teste
        $jogadores = [
            ['nome' => 'Rafael'],
            ['nome' => 'Henrique'],
            ['nome' => 'Lucas'],
            ['nome' => 'Tiago'],
            ['nome' => 'Jacó'],
            ['nome' => 'Sandro'],
            ['nome' => 'Omar'],
            ['nome' => 'Pedro'],
            ['nome' => 'João'],
            ['nome' => 'Maria'],
            ['nome' => 'Ana'],
            ['nome' => 'Carlos'],
            ['nome' => 'José'],
            ['nome' => 'Paulo'],
            ['nome' => 'Fernando'],
        ];

        foreach ($jogadores as $jogador) {
            Jogador::create($jogador);
        }
    }
}
