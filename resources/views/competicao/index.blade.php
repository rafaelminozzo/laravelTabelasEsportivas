@extends('layouts.layout')

@section('title', 'Página Inicial')

@section('content')
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
                    <a href="{{ route('competicao.editJogador', $jogador->id) }}" class="btn btn-warning btn-sm">Editar</a>
                    <form action="{{ route('competicao.destroyJogador', $jogador->id) }}" method="POST"
                        style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este jogador?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                    </form>
                </div>
            </li>
        @endforeach
    </ul>

    <div class="mt-4">
        <p><strong>Total de Jogadores Cadastrados:</strong> {{ $jogadores->count() }}</p>
    </div>

    <h2 class="mt-4">Gerar Tabela (Formato Copa)</h2>
    <form method="POST" action="{{ route('competicao.gerarTabela') }}">
        @csrf
        <button type="submit" class="btn btn-success">Gerar Tabela</button>
    </form>

    <!-- Lista de Jogadores com Ranking -->
    <ul class="list-group mt-3">
        @foreach ($ranking as $index => $item)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>
                    <strong>#{{ $index + 1 }}</strong>
                    {{ $item['nome'] }}
                </span>
                <span>Vitórias: {{ $item['vitorias'] }}</span>
            </li>
        @endforeach
    </ul>
    </div>

@endsection
