<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class RefeicaoController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $idFila = (int) $request->query('id_fila');
        if (!$idFila) {
            return redirect()->route('fila.index');
        }

        $item = $this->buscarItemFila($idFila);
        if (!$item) {
            return redirect()->route('fila.index')->with('error', 'Item da fila não encontrado.');
        }

        $cardapios = DB::select("
            SELECT ca.id_cardapio, ca.turno, ic.id_itens_cardapio,
                   ict.descricao AS tipo_item, ict.id_tipo,
                   ic.descricao AS descricao_item,
                   COALESCE(v.valor, 0) AS valor,
                   v.id_categoria_valores
            FROM cardapio ca
            JOIN itens_cardapio ic ON ic.id_cardapio = ca.id_cardapio
            JOIN itens_cardapio_tipo ict ON ict.id_tipo = ic.id_tipo
            LEFT JOIN itens_cardapio_tipo_categorias_valores v
                ON v.id_tipo = ict.id_tipo AND v.id_categoria = ?
            WHERE ca.data_servico = CURDATE()
            ORDER BY ca.turno, ict.descricao
        ", [$item->id_categoria]);

        return view('refeicao.create', [
            'title' => 'Registrar refeição',
            'item' => $item,
            'cardapios' => $cardapios,
            'sucesso' => false,
        ]);
    }

    public function store(Request $request): View|RedirectResponse
    {
        $data = $request->validate([
            'id_fila' => ['required', 'integer'],
            'id_itens_cardapio' => ['required', 'integer'],
            'id_categoria_valores' => ['required', 'integer'],
            'valor' => ['required', 'numeric', 'gt:0'],
        ]);

        $item = $this->buscarItemFila((int) $data['id_fila']);
        if (!$item) {
            return redirect()->route('fila.index')->with('error', 'Item da fila não encontrado.');
        }

        try {
            // Trigger debita carteira; procedure finaliza a fila
            DB::statement('CALL sp_registrar_refeicao(?, ?, ?, ?, ?)', [
                $item->id_usuario,
                $data['id_itens_cardapio'],
                $data['id_categoria_valores'],
                $data['valor'],
                $item->id_itens_fila,
            ]);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();
            if (str_contains($mensagem, 'Saldo insuficiente')) {
                $mensagem = 'Saldo insuficiente na carteira digital.';
            }

            $cardapios = DB::select("
                SELECT ca.id_cardapio, ca.turno, ic.id_itens_cardapio,
                       ict.descricao AS tipo_item, ict.id_tipo,
                       ic.descricao AS descricao_item,
                       COALESCE(v.valor, 0) AS valor,
                       v.id_categoria_valores
                FROM cardapio ca
                JOIN itens_cardapio ic ON ic.id_cardapio = ca.id_cardapio
                JOIN itens_cardapio_tipo ict ON ict.id_tipo = ic.id_tipo
                LEFT JOIN itens_cardapio_tipo_categorias_valores v
                    ON v.id_tipo = ict.id_tipo AND v.id_categoria = ?
                WHERE ca.data_servico = CURDATE()
                ORDER BY ca.turno, ict.descricao
            ", [$item->id_categoria]);

            $item = $this->buscarItemFila((int) $data['id_fila']);

            return view('refeicao.create', [
                'title' => 'Registrar refeição',
                'item' => $item,
                'cardapios' => $cardapios,
                'sucesso' => false,
                'erro' => $mensagem,
            ]);
        }

        $item = $this->buscarItemFila((int) $data['id_fila']);

        return view('refeicao.create', [
            'title' => 'Registrar refeição',
            'item' => $item,
            'cardapios' => [],
            'sucesso' => true,
        ]);
    }

    private function buscarItemFila(int $idFila): ?object
    {
        return DB::selectOne("
            SELECT i.id_itens_fila, i.id_usuario, i.situacao,
                   u.nome_completo, u.matricula, u.id_categoria,
                   cat.descricao AS categoria,
                   COALESCE(cd.saldo, 0) AS saldo,
                   f.tipo_fila
            FROM itens_fila i
            JOIN usuario u ON u.id_usuario = i.id_usuario
            JOIN categoria_usuario cat ON cat.id_categoria = u.id_categoria
            LEFT JOIN carteira_digital cd ON cd.id_usuario = u.id_usuario
            JOIN fila f ON f.id_fila = i.id_fila
            WHERE i.id_itens_fila = ?
        ", [$idFila]);
    }
}
