<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompeticaoController;

Route::get('/', [CompeticaoController::class, 'index'])->name('competicao.index');
Route::post('/jogadores', [CompeticaoController::class, 'store'])->name('competicao.store');
Route::post('/gerar-tabela', [CompeticaoController::class, 'gerarTabela'])->name('competicao.gerarTabela');
Route::get('/competicao/{id}/resultados', [CompeticaoController::class, 'resultados'])->name('competicao.resultados');
Route::post('/competicao/{id}/salvar-resultados', [CompeticaoController::class, 'salvarResultados'])->name('competicao.salvarResultados');

// Rota para exibir a fase eliminatória inicial
Route::get('/competicao/{id}/mata-mata', [CompeticaoController::class, 'gerarMataMataDaCopa'])->name('competicao.mataMata');

Route::get('/jogadores/{id}/edit', [CompeticaoController::class, 'editJogador'])->name('competicao.editJogador');
Route::put('/jogadores/{id}', [CompeticaoController::class, 'updateJogador'])->name('competicao.updateJogador');
Route::delete('/jogadores/{id}', [CompeticaoController::class, 'destroyJogador'])->name('competicao.destroyJogador');

// Rota para listar jogadores excluídos
Route::get('/jogadores/excluidos', [CompeticaoController::class, 'jogadoresExcluidos'])->name('competicao.jogadoresExcluidos');

// Rota para restaurar jogador
Route::post('/jogadores/{id}/restore', [CompeticaoController::class, 'restoreJogador'])->name('competicao.restoreJogador');


