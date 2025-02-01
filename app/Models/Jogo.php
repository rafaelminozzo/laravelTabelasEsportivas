<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jogo extends Model
{
    use HasFactory;

    protected $fillable = ['competicao_id', 'jogador1', 'jogador2', 'sets_jogador1', 'sets_jogador2'];
}
