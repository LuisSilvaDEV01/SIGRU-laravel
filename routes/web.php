<?php

use App\Http\Controllers\CarteiraController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstoqueController;
use App\Http\Controllers\FilaController;
use App\Http\Controllers\RefeicaoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\UsuarioController;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
Route::get('/usuarios/criar', [UsuarioController::class, 'create'])->name('usuarios.create');
Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
Route::get('/usuarios/{id}/editar', [UsuarioController::class, 'edit'])->name('usuarios.edit');
Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

Route::get('/fila', [FilaController::class, 'index'])->name('fila.index');
Route::post('/fila', [FilaController::class, 'store'])->name('fila.store');
Route::get('/fila/{id}/atender', [FilaController::class, 'atender'])->name('fila.atender');
Route::delete('/fila/{id}', [FilaController::class, 'destroy'])->name('fila.destroy');

Route::get('/refeicao', [RefeicaoController::class, 'create'])->name('refeicao.create');
Route::post('/refeicao', [RefeicaoController::class, 'store'])->name('refeicao.store');

Route::get('/carteiras', [CarteiraController::class, 'index'])->name('carteiras.index');
Route::post('/carteiras/recarregar', [CarteiraController::class, 'recarregar'])->name('carteiras.recarregar');
Route::post('/carteiras/ajustar', [CarteiraController::class, 'ajustar'])->name('carteiras.ajustar');

Route::get('/estoque', [EstoqueController::class, 'index'])->name('estoque.index');
Route::post('/estoque/produto', [EstoqueController::class, 'storeProduto'])->name('estoque.produto');
Route::post('/estoque/movimentar', [EstoqueController::class, 'movimentar'])->name('estoque.movimentar');

Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');

Route::get('/health', function () {
    $dashboard = DB::selectOne('SELECT * FROM vw_dashboard_dia');
    $usuarios = Usuario::count();
    $views = collect(DB::select("SHOW FULL TABLES WHERE Table_type = 'VIEW'"))
        ->map(fn ($row) => array_values((array) $row)[0])
        ->values();

    return response()->json([
        'app' => 'SIGRU',
        'framework' => 'Laravel',
        'database' => config('database.connections.mysql.database'),
        'connection' => 'ok',
        'usuarios_model' => $usuarios,
        'vw_dashboard_dia' => $dashboard,
        'views' => $views,
    ]);
});
