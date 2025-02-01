<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Editar Jogador</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
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
</body>
</html>
