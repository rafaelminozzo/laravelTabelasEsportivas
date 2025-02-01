<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Gerador de Tabelas Esportivas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center">Gerador de Tabelas Esportivas</h1>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Formulário para adicionar jogadores -->
        <form method="POST" action="{{ route('competicao.store') }}" class="mt-3">
            @csrf
            <div class="mb-3">
                <label for="nome" class="form-label">Nome do Jogador</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <button type="submit" class="btn btn-primary">Adicionar Jogador</button>
        </form>

        <!-- Título e botão de jogadores excluídos -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <h2>Jogadores Cadastrados</h2>
            <a href="{{ route('competicao.jogadoresExcluidos') }}" class="btn btn-secondary">Ver Jogadores Excluídos</a>
        </div>

        <!-- Lista de jogadores cadastrados -->
        <ul class="list-group mt-3">
            @foreach ($jogadores as $jogador)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{ $jogador->nome }}
                    <div>
                        <a href="{{ route('competicao.editJogador', $jogador->id) }}"
                            class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('competicao.destroyJogador', $jogador->id) }}" method="POST"
                            style="display:inline;"
                            onsubmit="return confirm('Tem certeza que deseja excluir este jogador?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>

        <h2 class="mt-4">Gerar Tabela (Formato Copa)</h2>
        <form method="POST" action="{{ route('competicao.gerarTabela') }}">
            @csrf
            <button type="submit" class="btn btn-success">Gerar Tabela</button>
        </form>

        <!-- Lista de Jogadores com Ranking -->
        <ul class="list-group mt-3">
            @foreach ($jogadores as $jogador)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>
                        <strong>#{{ $jogador->ranking }}</strong>
                        {{ $jogador->nome }}
                    </span>
                    <!-- Botões de edição/exclusão -->
                </li>
            @endforeach
        </ul>
    </div>
</body>

</html>
