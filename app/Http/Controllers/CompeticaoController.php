<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jogador;
use App\Models\Competicao;
use App\Models\Jogo;
use App\Models\Classificacao;

class CompeticaoController extends Controller
{
    public function index()
    {
        $jogadores = Jogador::all();

        // Calcula o ranking (exemplo simples)
        $ranking = [];
        foreach ($jogadores as $jogador) {
            $vitorias = Jogo::where('sets_jogador1', '>', 'sets_jogador2')
                ->orWhere('sets_jogador2', '>', 'sets_jogador1')
                ->where(function ($query) use ($jogador) {
                    $query->where('jogador1', $jogador->nome)
                        ->orWhere('jogador2', $jogador->nome);
                })
                ->count();

            $ranking[] = [
                'nome' => $jogador->nome,
                'vitorias' => $vitorias,
            ];
        }

        // Ordena o ranking por número de vitórias
        usort($ranking, function ($a, $b) {
            return $b['vitorias'] <=> $a['vitorias'];
        });

        return view('competicao.index', compact('jogadores', 'ranking'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required'
        ]);

        Jogador::create($request->only('nome'));
        return redirect()->route('competicao.index')->with('success', 'Jogador adicionado!');
    }

    public function gerarTabela(Request $request)
    {
        // Cria a competição com o formato fixo como 'copa'
        $competicao = Competicao::create([
            'nome'    => 'Competição ' . now()->format('d/m/Y H:i'),
            'formato' => 'copa'
        ]);

        $jogadores = Jogador::all();
        $total = $jogadores->count();

        if ($total < 3) {
            return redirect()->route('competicao.index')
                ->with('error', 'São necessários pelo menos 3 jogadores para a fase de grupos.');
        }

        // Ordena os jogadores pelo ranking (ordem crescente: #1 é o mais forte)
        $jogadores = $jogadores->sortBy('ranking');

        // Calcula os tamanhos dos grupos, priorizando grupos de 3 jogadores
        $groupSizes = [];
        if ($total % 3 == 0) {
            $groupSizes = array_fill(0, $total / 3, 3);
        } elseif ($total % 3 == 1) {
            $groupSizes = array_fill(0, floor($total / 3), 3);
            $groupSizes[0] = 4; // Um grupo terá 4 jogadores
        } else {
            $groupSizes = array_fill(0, floor($total / 3), 3);
            $groupSizes[count($groupSizes) - 1] = 4; // O último grupo terá 4 jogadores
        }

        // Distribui os jogadores nos grupos (considerando o ranking)
        $groups = [];
        $playersArray = $jogadores->values()->all(); // Converte para array indexado
        foreach ($groupSizes as $size) {
            $group = array_splice($playersArray, 0, $size);
            $groups[] = $group;
        }

        // Rótulos para os grupos (A, B, C...)
        $groupLabels = range('A', 'Z');

        // Criação dos jogos (round-robin) para cada grupo
        foreach ($groups as $index => $grupo) {
            $grupoLabel = $groupLabels[$index];
            foreach ($grupo as $i => $jogador1) {
                for ($j = $i + 1; $j < count($grupo); $j++) {
                    $jogador2 = $grupo[$j];
                    Jogo::create([
                        'competicao_id' => $competicao->id,
                        'jogador1'      => $jogador1->nome,
                        'jogador2'      => $jogador2->nome,
                        'grupo'         => $grupoLabel // Garante que o grupo seja preenchido
                    ]);
                }
            }
        }

        return redirect()->route('competicao.resultados', $competicao->id);
    }

    public function gerarMataMataDaCopa($id)
    {
        $competicao = Competicao::findOrFail($id);

        // Verificar se todos os jogos da fase de grupos têm resultados
        $jogosIncompletos = Jogo::where('competicao_id', $id)
            ->whereNotNull('grupo')
            ->where(function ($query) {
                $query->whereNull('sets_jogador1')
                    ->orWhereNull('sets_jogador2');
            })->exists();

        if ($jogosIncompletos) {
            return redirect()->back()->with('error', 'Alguns jogos da fase de grupos não têm resultados definidos.');
        }

        $classificacao = $this->calcularClassificacao($id);
        $avancados = [];

        foreach ($classificacao as $grupo => $jogadores) {
            if (count($jogadores) >= 2) {
                $avancados[] = $jogadores[0]; // Primeiro colocado
                $avancados[] = $jogadores[1]; // Segundo colocado
            }
        }

        $numTeams = count($avancados);
        $bracketSize = pow(2, ceil(log($numTeams, 2)));

        // Adicionar "Bye" se necessário
        while (count($avancados) < $bracketSize) {
            $avancados[] = 'Bye';
        }

        // Embaralhar para distribuir os grupos
        shuffle($avancados);

        // Gerar confrontos
        $matches = [];
        for ($i = 0; $i < $bracketSize / 2; $i++) {
            $jogador1 = $avancados[$i];
            $jogador2 = $avancados[$bracketSize - 1 - $i];
            $matches[] = [
                'jogador1' => $jogador1,
                'jogador2' => $jogador2,
            ];

            // Salvar no banco de dados
            Jogo::create([
                'competicao_id' => $competicao->id,
                'jogador1' => $jogador1,
                'jogador2' => $jogador2,
                'grupo' => 'Mata-Mata'
            ]);
        }

        return view('competicao.mata_mata', compact('matches', 'competicao'));
    }


    public function resultados($id)
    {
        $competicao = Competicao::findOrFail($id);

        // Obtém os jogos da competição, ordenados por grupo
        $jogos = Jogo::where('competicao_id', $id)
            ->orderBy('grupo')
            ->orderBy('id') // Ordena também por ID dentro de cada grupo
            ->get();

        // Agrupa os jogos por grupo
        $jogosPorGrupo = $jogos->groupBy('grupo');

        // Depuração: Verifica se os jogos estão sendo agrupados corretamente
        // dd($jogosPorGrupo);

        // Calcula a classificação dos grupos se o formato for 'copa'
        $classificacao = [];
        if ($competicao->formato == 'copa') {
            $classificacao = $this->calcularClassificacao($id);
        }

        return view('competicao.resultados', compact('competicao', 'jogosPorGrupo', 'classificacao'));
    }

    public function salvarResultados(Request $request, $id)
    {
        // Salva os resultados dos jogos
        $resultados = $request->input('resultados');
        foreach ($resultados as $jogoId => $res) {
            $jogo = Jogo::find($jogoId);
            if ($jogo) {
                $jogo->sets_jogador1 = $res['sets_jogador1'];
                $jogo->sets_jogador2 = $res['sets_jogador2'];
                $jogo->save();
            }
        }

        // Calcula a classificação final
        $classificacao = $this->calcularClassificacao($id);

        // Define a pontuação final com base na posição
        $posicoes = [13, 10, 7, 5, 4, 3, 2, 1];

        // Salva a classificação na tabela `classificacoes`
        foreach ($classificacao as $grupo => $jogadores) {
            foreach ($jogadores as $index => $jogador) {
                Classificacao::create([
                    'competicao_id' => $id,
                    'jogador'       => $jogador['nome'],
                    'posicao'       => $index + 1,
                    'pontos'        => $posicoes[$index] ?? 0,
                ]);
            }
        }

        return redirect()->route('competicao.resultados', $id)->with('success', 'Resultados atualizados!');
    }

    private function calcularClassificacao($competicao_id)
    {
        $jogos = Jogo::where('competicao_id', $competicao_id)
            ->whereNotNull('grupo')
            ->get();

        $classificacao = [];

        foreach ($jogos as $jogo) {
            $grupo = $jogo->grupo;

            foreach ([$jogo->jogador1, $jogo->jogador2] as $jogador) {
                if (!isset($classificacao[$grupo][$jogador])) {
                    $classificacao[$grupo][$jogador] = [
                        'pontos'      => 0,
                        'sets_favor'  => 0,
                        'sets_contra' => 0,
                    ];
                }
            }

            if ($jogo->sets_jogador1 !== null && $jogo->sets_jogador2 !== null) {
                $classificacao[$grupo][$jogo->jogador1]['sets_favor'] += $jogo->sets_jogador1;
                $classificacao[$grupo][$jogo->jogador1]['sets_contra'] += $jogo->sets_jogador2;
                $classificacao[$grupo][$jogo->jogador2]['sets_favor'] += $jogo->sets_jogador2;
                $classificacao[$grupo][$jogo->jogador2]['sets_contra'] += $jogo->sets_jogador1;

                if ($jogo->sets_jogador1 > $jogo->sets_jogador2) {
                    $classificacao[$grupo][$jogo->jogador1]['pontos'] += 3;
                } elseif ($jogo->sets_jogador1 < $jogo->sets_jogador2) {
                    $classificacao[$grupo][$jogo->jogador2]['pontos'] += 3;
                } else {
                    $classificacao[$grupo][$jogo->jogador1]['pontos'] += 1;
                    $classificacao[$grupo][$jogo->jogador2]['pontos'] += 1;
                }
            }
        }

        foreach ($classificacao as $grupo => &$jogadores) {
            uasort($jogadores, function ($a, $b) {
                // Ordenar por pontos, depois por saldo de sets
                return $b['pontos'] <=> $a['pontos'] ?: ($b['sets_favor'] - $b['sets_contra']) <=> ($a['sets_favor'] - $a['sets_contra']);
            });
            // Manter a ordem, mas extrair os nomes
            $jogadores = array_keys($jogadores);
        }

        return $classificacao;
    }

    public function jogadoresExcluidos()
    {
        // Busca os jogadores com Soft Delete
        $jogadoresExcluidos = Jogador::onlyTrashed()->get();

        // Retorna a view com os jogadores excluídos
        return view('competicao.jogadores_excluidos', compact('jogadoresExcluidos'));
    }


    public function restoreJogador($id)
    {
        $jogador = Jogador::withTrashed()->findOrFail($id); // Inclui deletados logicamente
        $jogador->restore(); // Restaura o jogador
        return redirect()->route('competicao.jogadoresExcluidos')->with('success', 'Jogador restaurado com sucesso!');
    }


    public function editJogador($id)
    {
        $jogador = Jogador::findOrFail($id); // Busca o jogador pelo ID
        return view('competicao.edit_jogador', compact('jogador')); // Envia o jogador para a view de edição
    }

    public function updateJogador(Request $request, $id)
    {
        $request->validate([
            'nome' => 'required' // Validação: nome é obrigatório
        ]);

        $jogador = Jogador::findOrFail($id); // Busca o jogador pelo ID
        $jogador->update($request->only('nome')); // Atualiza o nome do jogador

        return redirect()->route('competicao.index')->with('success', 'Jogador atualizado com sucesso!');
    }

    public function destroyJogador($id)
    {
        $jogador = Jogador::findOrFail($id); // Busca o jogador pelo ID
        $jogador->delete(); // Aplica Soft Delete ao jogador

        return redirect()->route('competicao.index')->with('success', 'Jogador excluído com sucesso!');
    }

    public function historico()
    {
        // Busca todas as competições com suas classificações
        $competicoes = Competicao::with('classificacoes')->get();

        return view('competicao.historico', compact('competicoes'));
    }
}
