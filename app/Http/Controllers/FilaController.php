<?php

namespace App\Http\Controllers;

use App\Models\Fila;
use App\Models\ItensFila;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FilaController extends Controller
{
    public function index(): View
    {
        $fila = DB::select("
            SELECT *
            FROM vw_fila_atual
            ORDER BY FIELD(situacao, 'atendendo', 'em espera', 'finalizado'), horario_inscricao
        ");

        $contadores = collect($fila)->groupBy('situacao')->map->count();

        return view('fila.index', [
            'title' => 'Fila virtual',
            'fila' => $fila,
            'cont' => $contadores,
            'usuarios' => Usuario::orderBy('nome_completo')->get(['id_usuario', 'nome_completo', 'matricula']),
            'tiposFila' => Fila::orderBy('id_fila')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_usuario' => ['required', 'integer', 'exists:usuario,id_usuario'],
            'id_fila' => ['required', 'integer', 'exists:fila,id_fila'],
        ]);

        $jaEmEspera = ItensFila::query()
            ->where('id_usuario', $data['id_usuario'])
            ->where('situacao', 'em espera')
            ->exists();

        if ($jaEmEspera) {
            return back()->with('error', 'Este usuário já está em espera na fila.');
        }

        ItensFila::create([
            'id_usuario' => $data['id_usuario'],
            'id_fila' => $data['id_fila'],
            'horario_inscricao' => now(),
            'situacao' => 'em espera',
        ]);

        return redirect()->route('fila.index')->with('success', 'Usuário adicionado à fila.');
    }

    public function atender(int $id): RedirectResponse
    {
        $item = ItensFila::findOrFail($id);
        $item->update(['situacao' => 'atendendo']);

        return redirect()->route('refeicao.create', ['id_fila' => $item->id_itens_fila]);
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = ItensFila::findOrFail($id);

        if ($item->situacao === 'finalizado') {
            return back()->with('error', 'Itens finalizados não podem ser removidos.');
        }

        $item->delete();

        return redirect()->route('fila.index')->with('success', 'Removido da fila.');
    }
}
