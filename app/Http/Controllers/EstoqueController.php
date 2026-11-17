<?php

namespace App\Http\Controllers;

use App\Models\Estoque;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\TipoProduto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class EstoqueController extends Controller
{
    public function index(): View
    {
        $estoque = DB::select('
            SELECT *
            FROM vw_estoque_status
            ORDER BY descricao_tipo, nome_produto
        ');

        $historico = DB::select('
            SELECT m.tipo_movimento, m.quantidade_mov, m.data_movimento,
                   p.nome_produto, p.unidade_medida, f.razao_social
            FROM movimento_estoque m
            JOIN produto p ON p.id_produto = m.id_produto
            LEFT JOIN fornecedores f ON f.cnpj = m.cnpj_fornecedor
            ORDER BY m.data_movimento DESC
            LIMIT 15
        ');

        return view('estoque.index', [
            'title' => 'Estoque',
            'estoque' => $estoque,
            'historico' => $historico,
            'produtos' => Produto::orderBy('nome_produto')->get(['id_produto', 'nome_produto', 'unidade_medida']),
            'fornecedores' => Fornecedor::orderBy('razao_social')->get(['cnpj', 'razao_social']),
            'tiposProduto' => TipoProduto::orderBy('descricao_tipo')->get(),
        ]);
    }

    public function storeProduto(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome_produto' => ['required', 'string', 'max:100'],
            'unidade_medida' => ['required', 'string', 'max:2'],
            'id_tipo_produto' => ['required', 'integer', 'exists:tipo_produto,id_tipo'],
            'quantidade_inicial' => ['nullable', 'integer', 'min:0'],
        ]);

        $qtdInicial = (int) ($data['quantidade_inicial'] ?? 0);

        try {
            DB::transaction(function () use ($data, $qtdInicial) {
                $produto = Produto::create([
                    'nome_produto' => $data['nome_produto'],
                    'unidade_medida' => $data['unidade_medida'],
                    'id_tipo_produto' => $data['id_tipo_produto'],
                ]);

                // Estoque começa zerado; entrada inicial via procedure/trigger
                Estoque::create([
                    'quantidade' => 0,
                    'data_atualizacao' => now(),
                    'id_produto' => $produto->id_produto,
                ]);

                if ($qtdInicial > 0) {
                    DB::statement('CALL sp_movimentar_estoque(?, ?, ?, ?)', [
                        'ENTRADA',
                        $qtdInicial,
                        $produto->id_produto,
                        null,
                    ]);
                }
            });
        } catch (Throwable $e) {
            return back()->with('error', $this->mensagemAmigavel($e->getMessage()))->withInput();
        }

        return redirect()
            ->route('estoque.index')
            ->with('success', 'Mercadoria "' . $data['nome_produto'] . '" cadastrada com sucesso.');
    }

    public function movimentar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_produto' => ['required', 'integer', 'exists:produto,id_produto'],
            'tipo_movimento' => ['required', 'in:ENTRADA,SAIDA'],
            'quantidade_mov' => ['required', 'integer', 'min:1'],
            'cnpj_fornecedor' => ['nullable', 'string', 'max:14'],
        ]);

        $cnpj = $data['cnpj_fornecedor'] ?: null;

        try {
            // Trigger atualiza a tabela estoque automaticamente
            DB::statement('CALL sp_movimentar_estoque(?, ?, ?, ?)', [
                $data['tipo_movimento'],
                $data['quantidade_mov'],
                $data['id_produto'],
                $cnpj,
            ]);
        } catch (Throwable $e) {
            return back()->with('error', $this->mensagemAmigavel($e->getMessage()))->withInput();
        }

        return redirect()
            ->route('estoque.index')
            ->with('success', 'Movimentação registrada via procedure (estoque atualizado pelo trigger).');
    }

    private function mensagemAmigavel(string $mensagem): string
    {
        if (str_contains($mensagem, 'Quantidade insuficiente')) {
            return 'Quantidade insuficiente em estoque para registrar esta saída.';
        }

        if (str_contains($mensagem, 'Tipo de movimento')) {
            return 'Tipo de movimentação inválido.';
        }

        if (str_contains($mensagem, 'Produto informado')) {
            return 'Produto informado não existe.';
        }

        return $mensagem;
    }
}
