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
                <h3>Grupo {{ $grupo }}</h3>
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
                                        value="{{ $jogo->sets_jogador1 ?? '' }}" class="form-control">
                                </td>
                                <td>
                                    <input type="number" name="resultados[{{ $jogo->id }}][sets_jogador2]"
                                        value="{{ $jogo->sets_jogador2 ?? '' }}" class="form-control">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Exibe a classificação parcial do grupo -->
                <h4>Classificação Parcial do Grupo {{ $grupo }}</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Posição</th>
                            <th>Jogador</th>
                            <th>Pontos</th>
                            <th>Sets a Favor</th>
                            <th>Sets Contra</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($classificacao[$grupo]))
                            @foreach ($classificacao[$grupo] as $index => $jogador)
                                <tr>
                                    <td>{{ $index + 1 }}º</td>
                                    <td>{{ $jogador['nome'] }}</td>
                                    <td>{{ $jogador['pontos'] }}</td>
                                    <td>{{ $jogador['sets_favor'] }}</td>
                                    <td>{{ $jogador['sets_contra'] }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            @endforeach

            <button type="submit" class="btn btn-primary">Salvar Resultados</button>
        </form>

        <!-- Exibe a classificação de cada grupo -->
        @foreach ($classificacao as $grupo => $jogadores)
            <h4>Classificação do Grupo {{ $grupo }}</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Posição</th>
                        <th>Jogador</th>
                        <th>Pontos</th>
                        <th>Sets a Favor</th>
                        <th>Sets Contra</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($jogadores as $index => $jogador)
                        <tr>
                            <td>{{ $index + 1 }}º</td>
                            <td>{{ $jogador['nome'] }}</td>
                            <td>{{ $jogador['pontos'] }}</td>
                            <td>{{ $jogador['sets_favor'] }}</td>
                            <td>{{ $jogador['sets_contra'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach

        <a href="{{ route('competicao.mataMata', $competicao->id) }}" class="btn btn-success mt-3">Avançar para a Fase
            Eliminatória</a>
        <a href="{{ route('competicao.index') }}" class="btn btn-secondary mt-3">Voltar</a>
    </div>
@endsection
