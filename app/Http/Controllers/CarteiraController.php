<?php

namespace App\Http\Controllers;

use App\Models\CarteiraDigital;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class CarteiraController extends Controller
{
    public function index(): View
    {
        $usuarios = Usuario::with('categoria')
            ->orderBy('nome_completo')
            ->get(['id_usuario', 'nome_completo', 'matricula', 'id_categoria']);

        $carteiras = DB::select('
            SELECT *
            FROM vw_carteiras_saldo
            ORDER BY nome_completo
        ');

        $historico = DB::select('
            SELECT rh.valor, rh.data, u.nome_completo, u.matricula
            FROM recarga_historico rh
            JOIN carteira_digital cd ON cd.id_carteira = rh.id_carteira
            JOIN usuario u ON u.id_usuario = cd.id_usuario
            ORDER BY rh.data DESC
            LIMIT 20
        ');

        return view('carteiras.index', [
            'title' => 'Carteiras',
            'usuarios' => $usuarios,
            'carteiras' => $carteiras,
            'historico' => $historico,
        ]);
    }

    public function recarregar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_usuario' => ['required', 'integer', 'exists:usuario,id_usuario'],
            'valor' => ['required', 'numeric', 'gt:0'],
        ]);

        try {
            DB::statement('CALL sp_recarregar_carteira(?, ?)', [
                $data['id_usuario'],
                $data['valor'],
            ]);
        } catch (Throwable $e) {
            return back()->with('error', $this->mensagemAmigavel($e->getMessage()));
        }

        return redirect()
            ->route('carteiras.index')
            ->with('success', 'Recarga de R$ ' . number_format((float) $data['valor'], 2, ',', '.') . ' realizada via procedure.');
    }

    public function ajustar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_carteira' => ['required', 'integer', 'exists:carteira_digital,id_carteira'],
            'novo_saldo' => ['required', 'numeric', 'min:0'],
        ]);

        CarteiraDigital::where('id_carteira', $data['id_carteira'])
            ->update(['saldo' => $data['novo_saldo']]);

        return redirect()
            ->route('carteiras.index')
            ->with('success', 'Saldo ajustado com sucesso.');
    }

    private function mensagemAmigavel(string $mensagem): string
    {
        if (str_contains($mensagem, 'Valor de recarga')) {
            return 'Informe um valor de recarga maior que zero.';
        }

        if (str_contains($mensagem, 'Usuario informado')) {
            return 'Usuário informado não existe.';
        }

        return $mensagem;
    }
}
