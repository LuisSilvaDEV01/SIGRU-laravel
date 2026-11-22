<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RelatorioController extends Controller
{
    public function index(Request $request): View
    {
        $hoje = now()->toDateString();
        $dataInicio = $request->input('data_inicio', $hoje);
        $dataFim = $request->input('data_fim', $hoje);

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $dataInicio)) {
            $dataInicio = $hoje;
        }
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $dataFim)) {
            $dataFim = $hoje;
        }
        if ($dataInicio > $dataFim) {
            [$dataInicio, $dataFim] = [$dataFim, $dataInicio];
        }

        $periodoLabel = $dataInicio === $dataFim
            ? \Illuminate\Support\Carbon::parse($dataInicio)->format('d/m/Y')
            : \Illuminate\Support\Carbon::parse($dataInicio)->format('d/m/Y')
                .' até '
                .\Illuminate\Support\Carbon::parse($dataFim)->format('d/m/Y');

        // Preferência: views avançadas quando cobrem o relatório
        $refeicoesCat = DB::select('
            SELECT categoria, SUM(qtd) AS qtd, SUM(total) AS total
            FROM vw_refeicoes_por_categoria
            WHERE data_refeicao BETWEEN ? AND ?
            GROUP BY categoria
            ORDER BY total DESC
        ', [$dataInicio, $dataFim]);

        $totalPeriodo = collect($refeicoesCat)->sum(fn ($r) => (float) $r->total);
        $qtdRefeicoes = collect($refeicoesCat)->sum(fn ($r) => (int) $r->qtd);

        $contasPagar = DB::select('
            SELECT *
            FROM vw_contas_pagar_pendentes
            WHERE data_vencimento BETWEEN ? AND ?
            ORDER BY data_vencimento
        ', [$dataInicio, $dataFim]);

        $totalPendente = collect($contasPagar)->sum(fn ($c) => (float) $c->valor);

        $carteiras = DB::select('
            SELECT nome_completo, categoria, saldo
            FROM vw_carteiras_saldo
            ORDER BY saldo DESC
        ');

        $contasReceber = DB::select('
            SELECT valor, data_prevista, origem
            FROM conta_receber
            WHERE data_prevista BETWEEN ? AND ?
            ORDER BY data_prevista
        ', [$dataInicio, $dataFim]);

        $totalReceber = collect($contasReceber)->sum(fn ($r) => (float) $r->valor);

        $movimentos = DB::select('
            SELECT p.nome_produto, m.tipo_movimento, m.quantidade_mov,
                   m.data_movimento, f.razao_social
            FROM movimento_estoque m
            JOIN produto p ON p.id_produto = m.id_produto
            LEFT JOIN fornecedores f ON f.cnpj = m.cnpj_fornecedor
            WHERE DATE(m.data_movimento) BETWEEN ? AND ?
            ORDER BY m.data_movimento DESC
        ', [$dataInicio, $dataFim]);

        return view('relatorios.index', [
            'title' => 'Relatórios',
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'periodoLabel' => $periodoLabel,
            'hoje' => $hoje,
            'refeicoesCat' => $refeicoesCat,
            'totalPeriodo' => $totalPeriodo,
            'qtdRefeicoes' => $qtdRefeicoes,
            'contasPagar' => $contasPagar,
            'totalPendente' => $totalPendente,
            'carteiras' => $carteiras,
            'contasReceber' => $contasReceber,
            'totalReceber' => $totalReceber,
            'movimentos' => $movimentos,
        ]);
    }
}
