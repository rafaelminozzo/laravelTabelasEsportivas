@extends('layouts.layout')

@section('title', 'Histórico de Competições')

@section('content')
<div class="container mt-5">
    <h1>Histórico de Competições</h1>

    @if ($competicoes->isEmpty())
        <p>Nenhuma competição registrada.</p>
    @else
        @foreach ($competicoes as $competicao)
            <div class="card mb-4">
                <div class="card-header">
                    <h3>{{ $competicao->nome }}</h3>
                    <p>Formato: {{ ucfirst($competicao->formato) }}</p>
                </div>
                <div class="card-body">
                    <h5>Classificação Final</h5>
                    <ul class="list-group">
                        @foreach ($competicao->classificacoes as $classificacao)
                            <li class="list-group-item">
                                <strong>{{ $classificacao->posicao }}º lugar:</strong>
                                {{ $classificacao->jogador }} - {{ $classificacao->pontos }} pontos
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection