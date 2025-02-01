<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Fase Mata-Mata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1>Fase Mata-Mata - {{ $competicao->nome }}</h1>

        <form method="POST" action="{{ route('competicao.salvarResultados', $competicao->id) }}">
            @csrf
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Jogador 1</th>
                        <th>Jogador 2</th>
                        <th>Sets Jogador 1</th>
                        <th>Sets Jogador 2</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($matches as $jogoId => $match)
                        <tr>
                            <td>{{ $match['jogador1'] }}</td>
                            <td>{{ $match['jogador2'] }}</td>
                            <td>
                                <input type="number" name="resultados[{{ $jogoId }}][sets_jogador1]"
                                    class="form-control" required>
                            </td>
                            <td>
                                <input type="number" name="resultados[{{ $jogoId }}][sets_jogador2]"
                                    class="form-control" required>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary">Salvar Resultados</button>
        </form>

        <a href="{{ route('competicao.resultados', $competicao->id) }}" class="btn btn-secondary mt-3">Voltar</a>
    </div>
</body>

</html>
