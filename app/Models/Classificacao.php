<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classificacao extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    // Define o nome da tabela manualmente
    protected $table = 'classificacoes';

    protected $fillable = [
        'competicao_id',
        'jogador',
        'posicao',
        'pontos',
    ];

    /**
     * Get the competicao associated with the classificacao.
     */
    public function competicao()
    {
        return $this->belongsTo(Competicao::class);
    }
}
