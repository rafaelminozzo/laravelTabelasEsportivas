@extends('layouts.layout')

@section('title', 'Editar Jogador')

@section('content')
    <div class="container mt-5">
        <h1>Editar Jogador</h1>
        <form method="POST" action="{{ route('competicao.updateJogador', $jogador->id) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="nome" class="form-label">Nome do Jogador</label>
                <input type="text" class="form-control" id="nome" name="nome" value="{{ $jogador->nome }}" required>
            </div>
            <button type="submit" class="btn btn-primary">Atualizar Jogador</button>
            <a href="{{ route('competicao.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
@endsection
