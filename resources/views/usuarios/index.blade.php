@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-title" style="justify-content:space-between">
        <span><i class="ti ti-users"></i> Usuários cadastrados</span>
        <a href="{{ route('usuarios.create') }}" class="btn btn-primary btn-sm">
            <i class="ti ti-plus"></i> Novo usuário
        </a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Matrícula</th>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Saldo carteira</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($usuarios as $u)
                    @php
                        $cat = $u->categoria->descricao ?? '';
                        $cls = match ($cat) {
                            'Aluno' => 'pill-purple',
                            'Bolsista' => 'pill-green',
                            'Servidor' => 'pill-amber',
                            default => 'pill-gray',
                        };
                        $saldo = (float) ($u->carteira->saldo ?? 0);
                    @endphp
                    <tr>
                        <td>{{ $u->matricula }}</td>
                        <td><strong>{{ $u->nome_completo }}</strong></td>
                        <td><span class="pill {{ $cls }}">{{ $cat }}</span></td>
                        <td>R$ {{ number_format($saldo, 2, ',', '.') }}</td>
                        <td class="flex gap-2">
                            <a href="{{ route('usuarios.edit', $u->id_usuario) }}" class="btn btn-secondary btn-sm">
                                <i class="ti ti-edit"></i> Editar
                            </a>
                            <form method="POST" action="{{ route('usuarios.destroy', $u->id_usuario) }}"
                                  onsubmit="return confirm('Excluir este usuário?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="ti ti-trash"></i> Excluir
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-muted" style="text-align:center;padding:1rem">
                            Nenhum usuário cadastrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
