@extends('layouts.layout')

@section('title', 'Fase Eliminatória - Mata-Mata')

@section('content')
    <div class="container mt-5">
        <h1>Fase Eliminatória - {{ $fase }}</h1>
        <p>Competição: {{ ucfirst($competicao->formato) }}</p>

        <h3 class="mt-4">Confrontos</h3>
        <form method="POST" action="{{ route('competicao.salvarResultadosMataMata', $competicao->id) }}">
            @csrf
            <ul class="list-group">
                @foreach ($matches as $index => $match)
                    <li class="list-group-item">
                        @if ($match['jogador1'] === 'Bye')
                            <strong>{{ $match['jogador2'] }}</strong> avança automaticamente.
                        @elseif ($match['jogador2'] === 'Bye')
                            <strong>{{ $match['jogador1'] }}</strong> avança automaticamente.
                        @else
                            <div class="row">
                                <div class="col-md-5">
                                    {{ $match['jogador1'] }}
                                    <input type="number" name="resultados[{{ $index }}][sets_jogador1]"
                                        class="form-control" placeholder="Sets">
                                </div>
                                <div class="col-md-2 text-center">vs</div>
                                <div class="col-md-5">
                                    {{ $match['jogador2'] }}
                                    <input type="number" name="resultados[{{ $index }}][sets_jogador2]"
                                        class="form-control" placeholder="Sets">
                                </div>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
            <button type="submit" class="btn btn-primary mt-3">Salvar Resultados e Avançar</button>
        </form>

        <a href="{{ route('competicao.resultados', $competicao->id) }}" class="btn btn-secondary mt-3">Voltar para os
            Resultados</a>
    </div>
@endsection
