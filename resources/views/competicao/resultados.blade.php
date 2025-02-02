@extends('layouts.layout')

@section('title', 'Resultados da Competição')

@section('content')
    <div class="container mt-5">
        <h1>Resultados da Competição</h1>
        <h2>{{ $competicao->nome }}</h2>
        <p>Formato: {{ ucfirst($competicao->formato) }}</p>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('competicao.salvarResultados', $competicao->id) }}">
            @csrf

            <!-- Exibe os jogos agrupados por grupo -->
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
                                        value="{{ $jogo->sets_jogador1 ?? '' }}" class="form-control" required>
                                </td>
                                <td>
                                    <input type="number" name="resultados[{{ $jogo->id }}][sets_jogador2]"
                                        value="{{ $jogo->sets_jogador2 ?? '' }}" class="form-control" required>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach

            <button type="submit" class="btn btn-primary">Salvar Resultados</button>
        </form>

        <a href="{{ route('competicao.mataMata', $competicao->id) }}" class="btn btn-success mt-3">Avançar para a Fase
            Eliminatória</a>
        <a href="{{ route('competicao.index') }}" class="btn btn-secondary mt-3">Voltar</a>
    </div>
@endsection
