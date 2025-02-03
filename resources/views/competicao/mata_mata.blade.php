@extends('layouts.layout')

@section('title', 'Fase Eliminatória - Mata-Mata')

@section('content')
    <div class="container mt-5">
        <h1>Fase Mata-Mata - {{ $competicao->nome }}</h1>
        <p>Competição: {{ ucfirst($competicao->formato) }}</p>

        <h3 class="mt-4">Chave de Confrontos</h3>
        <div class="row">
            @foreach ($matches as $index => $match)
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Confronto {{ $index + 1 }}</h5>
                            <p>{{ $match['jogador1'] }} vs {{ $match['jogador2'] }}</p>
                            <form method="POST" action="{{ route('competicao.salvarConfronto') }}">
                                @csrf
                                <input type="hidden" name="competicao_id" value="{{ $competicao->id }}">
                                <input type="hidden" name="jogador1" value="{{ $match['jogador1'] }}">
                                <input type="hidden" name="jogador2" value="{{ $match['jogador2'] }}">
                                <div class="mb-3">
                                    <label for="sets_jogador1" class="form-label">Sets {{ $match['jogador1'] }}</label>
                                    <input type="number" class="form-control" id="sets_jogador1" name="sets_jogador1"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="sets_jogador2" class="form-label">Sets {{ $match['jogador2'] }}</label>
                                    <input type="number" class="form-control" id="sets_jogador2" name="sets_jogador2"
                                        required>
                                </div>
                                <button type="submit" class="btn btn-primary">Salvar Resultado</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
