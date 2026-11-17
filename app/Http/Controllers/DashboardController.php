<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $dash = DB::selectOne('SELECT * FROM vw_dashboard_dia');

        $ultimasRefeicoes = DB::select('
            SELECT nome_completo, turno, data_servico, valor, horario_entrada, horario_saida
            FROM vw_ultimas_refeicoes
            ORDER BY horario_entrada DESC
            LIMIT 6
        ');

        $estoque = DB::select('
            SELECT nome_produto, unidade_medida, quantidade, status_estoque
            FROM vw_estoque_status
            ORDER BY quantidade ASC
            LIMIT 6
        ');

        $cardapioDia = DB::select('
            SELECT turno, tipo_item, detalhe
            FROM vw_cardapio_hoje
            ORDER BY turno, tipo_item
        ');

        $cardapioAgrupado = collect($cardapioDia)->groupBy('turno');

        return view('dashboard', [
            'title' => 'Dashboard',
            'dash' => $dash,
            'ultimasRefeicoes' => $ultimasRefeicoes,
            'estoque' => $estoque,
            'cardapioAgrupado' => $cardapioAgrupado,
        ]);
    }
}
