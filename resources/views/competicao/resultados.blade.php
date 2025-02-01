<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Resultados da Competição</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .grupo-table {
            margin-bottom: 30px;
        }

        h4.grupo-titulo {
            margin-top: 25px;
            color: #2c3e50;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1>Resultados da Competição</h1>
        <h2>{{ $competicao->nome }}</h2>
        <p>Formato: {{ ucfirst($competicao->formato) }}</p>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('competicao.salvarResultados', $competicao->id) }}">
            @csrf

            @foreach ($jogosPorGrupo as $grupo => $jogos)
                <h3 class="mt-4">Grupo {{ $grupo }}</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Jogador 1</th>
                            <th>Jogador 2</th>
                            <th>Sets (J1)</th>
                            <th>Sets (J2)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jogos as $jogo)
                            <tr>
                                <td>{{ $jogo->id }}</td>
                                <td>{{ $jogo->jogador1 }}</td>
                                <td>{{ $jogo->jogador2 }}</td>
                                <td>
                                    <input type="number" name="resultados[{{ $jogo->id }}][sets_jogador1]"
                                        value="{{ $jogo->sets_jogador1 }}" class="form-control" required>
                                </td>
                                <td>
                                    <input type="number" name="resultados[{{ $jogo->id }}][sets_jogador2]"
                                        value="{{ $jogo->sets_jogador2 }}" class="form-control" required>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach

            <button type="submit" class="btn btn-primary">Salvar Resultados</button>
        </form>

        @if ($competicao->formato == 'copa' && !empty($classificacao))
            <!-- Seção de classificação (opcional) -->
        @endif
    </div>
</body>

</html>
