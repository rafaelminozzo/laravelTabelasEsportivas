@extends('layouts.layout')

@section('title', 'Excluídos')

@section('content')
    <div class="container mt-5">
        <h1>Jogadores Excluídos</h1>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jogadoresExcluidos as $jogador)
                    <tr>
                        <td>{{ $jogador->id }}</td>
                        <td>{{ $jogador->nome }}</td>
                        <td>
                            <form action="{{ route('competicao.restoreJogador', $jogador->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Restaurar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">Nenhum jogador excluído encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <a href="{{ route('competicao.index') }}" class="btn btn-secondary mt-3">Voltar</a>
    </div>

@endsection
