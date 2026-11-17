@extends('layouts.app')

@section('content')
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem">
    <div class="card" style="margin-bottom:0">
        <div class="card-title"><i class="ti ti-plus"></i> Recarregar carteira</div>
        <form method="POST" action="{{ route('carteiras.recarregar') }}">
            @csrf
            <div class="form-group" style="margin-bottom:1rem">
                <label>Usuário *</label>
                <select name="id_usuario" required>
                    <option value="">Selecione o usuário...</option>
                    @foreach ($usuarios as $u)
                        <option value="{{ $u->id_usuario }}" @selected(old('id_usuario') == $u->id_usuario)>
                            {{ $u->nome_completo }} — {{ $u->categoria->descricao ?? '' }} ({{ $u->matricula }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom:1rem">
                <label>Valor da recarga (R$) *</label>
                <input type="number" name="valor" min="0.01" step="0.01" placeholder="Ex: 20.00" value="{{ old('valor') }}" required>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-wallet"></i> Confirmar recarga
            </button>
        </form>
    </div>

    <div class="card" style="margin-bottom:0">
        <div class="card-title"><i class="ti ti-history"></i> Recargas recentes</div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>Valor</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($historico as $h)
                        <tr>
                            <td>
                                <strong>{{ $h->nome_completo }}</strong><br>
                                <span class="text-muted">{{ $h->matricula }}</span>
                            </td>
                            <td style="color:var(--teal-600);font-weight:600">
                                + R$ {{ number_format((float) $h->valor, 2, ',', '.') }}
                            </td>
                            <td class="text-muted">{{ \Illuminate\Support\Carbon::parse($h->data)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted" style="text-align:center;padding:1rem">Sem recargas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-title"><i class="ti ti-credit-card"></i> Saldo das carteiras digitais</div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>Matrícula</th>
                    <th>Categoria</th>
                    <th>Saldo atual</th>
                    <th>Status</th>
                    <th>Ajustar saldo</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($carteiras as $c)
                    @php
                        $saldo = (float) $c->saldo;
                        $cls = match ($c->categoria) {
                            'Aluno' => 'pill-purple',
                            'Bolsista' => 'pill-green',
                            'Servidor' => 'pill-amber',
                            default => 'pill-gray',
                        };
                    @endphp
                    <tr>
                        <td><strong>{{ $c->nome_completo }}</strong></td>
                        <td class="text-muted">{{ $c->matricula }}</td>
                        <td><span class="pill {{ $cls }}">{{ $c->categoria }}</span></td>
                        <td style="font-size:15px;font-weight:600;color:{{ $saldo <= 0 ? 'var(--red-600)' : ($saldo < 10 ? 'var(--amber-600)' : 'var(--teal-600)') }}">
                            R$ {{ number_format($saldo, 2, ',', '.') }}
                        </td>
                        <td>
                            @if ($saldo <= 0)
                                <span class="pill pill-red">Sem saldo</span>
                            @elseif ($saldo < 10)
                                <span class="pill pill-amber">Saldo baixo</span>
                            @else
                                <span class="pill pill-green">OK</span>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('carteiras.ajustar') }}" style="display:flex;gap:6px;align-items:center"
                                  onsubmit="return confirm('Ajustar saldo para o valor informado?')">
                                @csrf
                                <input type="hidden" name="id_carteira" value="{{ $c->id_carteira }}">
                                <input type="number" name="novo_saldo" min="0" step="0.01"
                                       value="{{ number_format($saldo, 2, '.', '') }}"
                                       style="width:90px;padding:5px 8px;font-size:12px;border:1px solid var(--border-md);border-radius:var(--radius-md)">
                                <button type="submit" class="btn btn-secondary btn-sm"><i class="ti ti-check"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
