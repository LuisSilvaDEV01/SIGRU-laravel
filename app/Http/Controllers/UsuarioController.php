<?php

namespace App\Http\Controllers;

use App\Models\Bairro;
use App\Models\CarteiraDigital;
use App\Models\CategoriaUsuario;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class UsuarioController extends Controller
{
    public function index(): View
    {
        $usuarios = Usuario::query()
            ->with(['categoria', 'carteira'])
            ->orderBy('nome_completo')
            ->get();

        return view('usuarios.index', [
            'title' => 'Usuários',
            'usuarios' => $usuarios,
        ]);
    }

    public function create(): View
    {
        return view('usuarios.form', [
            'title' => 'Cadastrar usuário',
            'usuario' => new Usuario,
            'categorias' => CategoriaUsuario::orderBy('descricao')->get(),
            'bairros' => $this->bairrosComCidade(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        try {
            DB::transaction(function () use ($data) {
                $usuario = Usuario::create($data);

                CarteiraDigital::create([
                    'id_usuario' => $usuario->id_usuario,
                    'saldo' => 0,
                ]);
            });
        } catch (Throwable $e) {
            return back()->with('error', 'Não foi possível cadastrar: '.$e->getMessage())->withInput();
        }

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário cadastrado com carteira digital (saldo R$ 0,00).');
    }

    public function edit(int $id): View|RedirectResponse
    {
        $usuario = Usuario::findOrFail($id);

        return view('usuarios.form', [
            'title' => 'Editar usuário',
            'usuario' => $usuario,
            'categorias' => CategoriaUsuario::orderBy('descricao')->get(),
            'bairros' => $this->bairrosComCidade(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $usuario = Usuario::findOrFail($id);
        $data = $this->validated($request, $usuario->id_usuario);

        $usuario->update($data);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $usuario = Usuario::findOrFail($id);

        try {
            DB::transaction(function () use ($usuario) {
                // Remove carteira se existir; demais FKs podem bloquear
                CarteiraDigital::where('id_usuario', $usuario->id_usuario)->delete();
                $usuario->delete();
            });
        } catch (Throwable $e) {
            return back()->with(
                'error',
                'Não foi possível excluir: o usuário possui vínculos (fila, refeições etc.).'
            );
        }

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário excluído com sucesso.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'matricula' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('usuario', 'matricula')->ignore($ignoreId, 'id_usuario'),
            ],
            'nome_completo' => ['required', 'string', 'max:100'],
            'id_categoria' => ['required', 'integer', 'exists:categoria_usuario,id_categoria'],
            'id_bairro' => ['required', 'integer', 'exists:bairro,id_bairro'],
            'endereco' => ['required', 'string', 'max:100'],
        ]);
    }

    private function bairrosComCidade()
    {
        return Bairro::query()
            ->with('cidade')
            ->join('cidade', 'cidade.id_cidade', '=', 'bairro.id_cidade')
            ->orderBy('cidade.descricao')
            ->orderBy('bairro.descricao')
            ->select('bairro.*')
            ->get();
    }
}
