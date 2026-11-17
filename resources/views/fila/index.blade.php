@extends('layouts.app')

@section('content')
<div class="metric-grid">
    <div class="metric-card accent">
        <div class="metric-label">Em espera</div>
        <div class="metric-value">{{ $cont['em espera'] ?? 0 }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Em atendimento</div>
        <div class="metric-value">{{ $cont['atendendo'] ?? 0 }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Finalizados</div>
        <div class="metric-value">{{ $cont['finalizado'] ?? 0 }}</div>
    </div>
</div>

<div class="card">
    <div class="card-title"><i class="ti ti-user-plus"></i> Adicionar usuário à fila</div>
    <form method="POST" action="{{ route('fila.store') }}">
        @csrf
        <div class="form-grid">
            <div class="form-group">
                <label>Usuário *</label>
                <select name="id_usuario" required>
                    <option value="">Selecione o usuário...</option>
                    @foreach ($usuarios as $u)
                        <option value="{{ $u->id_usuario }}">{{ $u->nome_completo }} — {{ $u->matricula }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Tipo de fila *</label>
                <select name="id_fila" required>
                    <option value="">Selecione...</option>
                    @foreach ($tiposFila as $f)
                        <option value="{{ $f->id_fila }}">{{ $f->tipo_fila }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div style="margin-top:1rem">
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-plus"></i> Adicionar à fila
            </button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-title"><i class="ti ti-list-numbers"></i> Fila virtual — situação atual</div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Pos.</th>
                    <th>Usuário</th>
                    <th>Categoria</th>
                    <th>Tipo de fila</th>
                    <th>Entrada</th>
                    <th>Situação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @php $pos = 1; @endphp
                @forelse ($fila as $r)
                    @php
                        $emEspera = $r->situacao === 'em espera';
                        $atendendo = $r->situacao === 'atendendo';
                        $finalizado = $r->situacao === 'finalizado';
                        $cls = match ($r->categoria) {
                            'Aluno' => 'pill-purple',
                            'Bolsista' => 'pill-green',
                            'Servidor' => 'pill-amber',
                            default => 'pill-gray',
                        };
                    @endphp
                    <tr>
                        <td>{{ ($emEspera || $atendendo) ? $pos++ : '—' }}</td>
                        <td>
                            <strong>{{ $r->nome_completo }}</strong><br>
                            <span class="text-muted">{{ $r->matricula }}</span>
                        </td>
                        <td><span class="pill {{ $cls }}">{{ $r->categoria }}</span></td>
                        <td>{{ $r->tipo_fila }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($r->horario_inscricao)->format('H:i') }}</td>
                        <td>
                            @if ($emEspera)
                                <span class="pill pill-amber">Em espera</span>
                            @elseif ($atendendo)
                                <span class="pill pill-teal">Atendendo</span>
                            @else
                                <span class="pill pill-green">Finalizado</span>
                            @endif
                        </td>
                        <td class="flex gap-2">
                            @if ($emEspera)
                                <a href="{{ route('fila.atender', $r->id_itens_fila) }}" class="btn btn-primary btn-sm">
                                    <i class="ti ti-check"></i> Atender
                                </a>
                            @endif
                            @if ($atendendo)
                                <a href="{{ route('refeicao.create', ['id_fila' => $r->id_itens_fila]) }}" class="btn btn-primary btn-sm">
                                    <i class="ti ti-tools-kitchen-2"></i> Registrar refeição
                                </a>
                            @endif
                            @if (!$finalizado)
                                <form method="POST" action="{{ route('fila.destroy', $r->id_itens_fila) }}" onsubmit="return confirm('Remover da fila?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="ti ti-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted" style="text-align:center;padding:1.5rem">Fila vazia.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
