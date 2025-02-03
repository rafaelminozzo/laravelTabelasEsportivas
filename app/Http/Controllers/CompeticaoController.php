<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jogador;
use App\Models\Competicao;
use App\Models\Jogo;
use App\Models\Classificacao;
use App\Models\Confronto;

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
        // Validação para garantir que o nome seja único
        $request->validate([
            'nome' => 'required|unique:jogadors,nome|string|max:20',
        ]);

        // Verifica se o jogador já existe
        $jogadorExistente = Jogador::where('nome', $request->input('nome'))->first();

        if ($jogadorExistente) {
            return redirect()->route('competicao.index')
                ->with('error', 'O jogador "' . $request->input('nome') . '" já está cadastrado.');
        }

        // Cria o jogador
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

    public function gerarMataMataDaCopa($competicao_id)
    {
        // Calcula a classificação final da fase de grupos
        $classificacao = $this->calcularClassificacao($competicao_id);

        $avancados = [];
        // Pega os dois primeiros de cada grupo
        foreach ($classificacao as $grupo => $jogadores) {
            if (count($jogadores) >= 2) {
                $avancados[] = ['nome' => $jogadores[0]['nome'], 'grupo' => $grupo];
                $avancados[] = ['nome' => $jogadores[1]['nome'], 'grupo' => $grupo];
            }
        }

        // Completa o número de times para formar uma chave de mata-mata válida
        $numTeams = count($avancados);
        $bracketSize = pow(2, ceil(log($numTeams, 2)));
        while (count($avancados) < $bracketSize) {
            $avancados[] = ['nome' => 'Bye'];
        }

        // Cria os confrontos iniciais
        $matches = [];
        for ($i = 0; $i < $bracketSize / 2; $i++) {
            $matches[] = [
                'jogador1' => $avancados[$i]['nome'] ?? 'Bye',
                'jogador2' => $avancados[$bracketSize - 1 - $i]['nome'] ?? 'Bye',
            ];

            // Salva os confrontos na tabela `confrontos`
            Confronto::create([
                'competicao_id' => $competicao_id,
                'fase'          => $this->getFaseAtual($bracketSize / 2),
                'jogador1'      => $matches[$i]['jogador1'],
                'jogador2'      => $matches[$i]['jogador2'],
            ]);
        }

        // Determina a fase atual com base no número de confrontos
        $fase = $this->getFaseAtual($bracketSize / 2);

        // Busca a competição pelo ID
        $competicao = Competicao::findOrFail($competicao_id);

        // Retorna a view com os dados necessários
        return view('competicao.mata_mata', compact('matches', 'competicao', 'fase'));
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
        $resultados = $request->input('resultados');

        foreach ($resultados as $jogoId => $res) {
            $jogo = Jogo::find($jogoId);
            if ($jogo) {
                $jogo->sets_jogador1 = $res['sets_jogador1'];
                $jogo->sets_jogador2 = $res['sets_jogador2'];
                $jogo->save();
            }
        }

        // Calcula a classificação parcial
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

    public function salvarResultadosDoMataMata(Request $request, $competicao_id)
    {
        $resultados = $request->input('resultados');

        foreach ($resultados as $matchId => $resultado) {
            $match = Confronto::find($matchId);

            if ($match) {
                // Verifica se algum dos jogadores é um "Bye"
                if ($match->jogador1 === 'Bye') {
                    $match->sets_jogador1 = 0;
                    $match->sets_jogador2 = 3; // Jogador 2 avança automaticamente
                } elseif ($match->jogador2 === 'Bye') {
                    $match->sets_jogador1 = 3; // Jogador 1 avança automaticamente
                    $match->sets_jogador2 = 0;
                } else {
                    // Salva os resultados normais
                    $match->sets_jogador1 = $resultado['sets_jogador1'];
                    $match->sets_jogador2 = $resultado['sets_jogador2'];
                }

                $match->save();
            }
        }

        return redirect()->back()->with('success', 'Resultados salvos!');
    }

    private function calcularClassificacao($competicao_id)
    {
        $jogos = Jogo::where('competicao_id', $competicao_id)
            ->whereNotNull('grupo')
            ->get();

        $classificacao = [];
        foreach ($jogos as $jogo) {
            $grupo = $jogo->grupo;

            // Inicializa os jogadores no grupo, se ainda não existirem
            foreach ([$jogo->jogador1, $jogo->jogador2] as $jogador) {
                if (!isset($classificacao[$grupo][$jogador])) {
                    $classificacao[$grupo][$jogador] = [
                        'nome'        => $jogador,
                        'pontos'      => 0,
                        'sets_favor'  => 0,
                        'sets_contra' => 0,
                    ];
                }
            }

            // Atualiza os sets e pontos com base nos resultados
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

        // Ordena os jogadores de cada grupo por pontos
        foreach ($classificacao as $grupo => &$jogadores) {
            usort($jogadores, function ($a, $b) {
                return $b['pontos'] <=> $a['pontos'];
            });
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

    public function salvarConfronto(Request $request)
    {
        $request->validate([
            'competicao_id' => 'required|exists:competicaos,id',
            'jogador1'      => 'nullable|string',
            'jogador2'      => 'nullable|string',
            'sets_jogador1' => 'nullable|integer',
            'sets_jogador2' => 'nullable|integer',
        ]);

        Confronto::create([
            'competicao_id' => $request->competicao_id,
            'fase'          => 'oitavas', // Defina a fase correta aqui
            'jogador1'      => $request->jogador1,
            'jogador2'      => $request->jogador2,
            'sets_jogador1' => $request->sets_jogador1,
            'sets_jogador2' => $request->sets_jogador2,
        ]);

        return redirect()->back()->with('success', 'Resultado do confronto salvo!');
    }

    public function salvarResultadosMataMata(Request $request, $competicao_id)
    {
        $resultados = $request->input('resultados');

        // Array para armazenar os jogadores que avançam para a próxima fase
        $vencedores = [];

        // Recupera os confrontos da fase atual
        $confrontos = Confronto::where('competicao_id', $competicao_id)
            ->whereNull('sets_jogador1') // Apenas confrontos sem resultados salvos
            ->whereNull('sets_jogador2')
            ->get();

        foreach ($confrontos as $index => $confronto) {
            // Verifica se o resultado para este confronto foi enviado
            if (!isset($resultados[$index]['sets_jogador1']) || !isset($resultados[$index]['sets_jogador2'])) {
                return redirect()->back()->with('error', 'Por favor, preencha todos os resultados.');
            }

            $sets_jogador1 = $resultados[$index]['sets_jogador1'];
            $sets_jogador2 = $resultados[$index]['sets_jogador2'];

            // Atualiza o confronto com os resultados
            $confronto->update([
                'sets_jogador1' => $sets_jogador1,
                'sets_jogador2' => $sets_jogador2,
            ]);

            // Verifica quem venceu o confronto
            if ($sets_jogador1 > $sets_jogador2) {
                $vencedores[] = $confronto->jogador1;
            } elseif ($sets_jogador1 < $sets_jogador2) {
                $vencedores[] = $confronto->jogador2;
            } else {
                // Em caso de empate, decide por critérios adicionais (ex.: ranking)
                // Aqui, vamos assumir que o jogador 1 avança
                $vencedores[] = $confronto->jogador1;
            }
        }

        // Gera a próxima fase com base nos vencedores
        return $this->gerarProximaFase($competicao_id, $vencedores);
    }

    private function gerarProximaFase($competicao_id, $vencedores)
    {
        $numTeams = count($vencedores);
        $bracketSize = pow(2, ceil(log($numTeams, 2)));

        // Completa o número de times para formar uma chave válida
        while (count($vencedores) < $bracketSize) {
            $vencedores[] = 'Bye';
        }

        // Cria os confrontos da próxima fase
        $matches = [];
        for ($i = 0; $i < $bracketSize / 2; $i++) {
            $matches[] = [
                'jogador1' => $vencedores[$i] ?? 'Bye',
                'jogador2' => $vencedores[$bracketSize - 1 - $i] ?? 'Bye',
            ];

            // Salva os confrontos na tabela `confrontos`
            Confronto::create([
                'competicao_id' => $competicao_id,
                'fase'          => $this->getFaseAtual($bracketSize / 2),
                'jogador1'      => $matches[$i]['jogador1'],
                'jogador2'      => $matches[$i]['jogador2'],
            ]);
        }

        // Determina a fase atual com base no número de confrontos
        $fase = $this->getFaseAtual($bracketSize / 2);

        // Busca a competição pelo ID
        $competicao = Competicao::findOrFail($competicao_id);

        // Retorna a view com os dados da próxima fase
        return view('competicao.mata_mata', compact('matches', 'competicao', 'fase'));
    }

    private function getFaseAtual($numMatches)
    {
        return match ($numMatches) {
            32 => 'Fase de 32',
            16 => 'Oitavas de Final',
            8  => 'Quartas de Final',
            4  => 'Semifinal',
            2  => 'Final',
            default => 'Fase Inicial',
        };
    }
}
