<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Confronto extends Model
{
    use HasFactory;

    protected $fillable = [
        'competicao_id',
        'fase',
        'jogador1',
        'jogador2',
        'sets_jogador1',
        'sets_jogador2',
    ];
}