<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Jogadores Excluídos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <h1>Jogadores Excluídos</h1>

    @if(session('success'))
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
</body>
</html>
