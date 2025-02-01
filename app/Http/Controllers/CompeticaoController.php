<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jogador;
use App\Models\Competicao;
use App\Models\Jogo;

class CompeticaoController extends Controller
{
    public function index()
    {
        $jogadores = Jogador::orderBy('ranking', 'asc')->get(); // Ordena do mais forte (1) para o mais fraco
        return view('competicao.index', compact('jogadores'));
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
        $competicao = Competicao::create([
            'nome' => 'Competição ' . now()->format('d/m/Y H:i'),
            'formato' => 'copa'
        ]);

        $jogadores = Jogador::orderBy('ranking', 'asc')->get();
        $total = $jogadores->count();

        // Número de grupos (ex: 6 jogadores → 2 grupos)
        $numGrupos = max(1, floor($total / 3));
        $grupos = [];
        $groupLabels = range('A', 'Z');

        // Distribuição balanceada (1º, 3º, 5º no Grupo A | 2º, 4º, 6º no Grupo B)
        for ($i = 0; $i < $numGrupos; $i++) {
            $grupos[$groupLabels[$i]] = [];
        }

        foreach ($jogadores as $index => $jogador) {
            $grupoIndex = $index % $numGrupos; // Distribui sequencialmente
            $grupoLabel = $groupLabels[$grupoIndex];
            $grupos[$grupoLabel][] = $jogador;
        }

        // Cria os jogos (round-robin dentro de cada grupo)
        foreach ($grupos as $grupoLabel => $jogadoresGrupo) {
            foreach ($jogadoresGrupo as $i => $jogador1) {
                for ($j = $i + 1; $j < count($jogadoresGrupo); $j++) {
                    $jogador2 = $jogadoresGrupo[$j];
                    Jogo::create([
                        'competicao_id' => $competicao->id,
                        'jogador1' => $jogador1->nome,
                        'jogador2' => $jogador2->nome,
                        'grupo' => $grupoLabel
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

        // Obtem os jogos da competição, ordenados por grupo
        $jogos = Jogo::where('competicao_id', $id)
            ->orderBy('grupo')
            ->orderBy('id') // Ordena também por ID dentro de cada grupo
            ->get();

        // Agrupa os jogos por grupo
        $jogosPorGrupo = $jogos->groupBy('grupo');

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
}
