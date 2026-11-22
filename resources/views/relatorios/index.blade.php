@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-title"><i class="ti ti-calendar-stats"></i> Filtrar relatórios por período</div>
    <form method="GET" action="{{ route('relatorios.index') }}" class="flex gap-2" style="align-items:flex-end;flex-wrap:wrap">
        <div class="form-group">
            <label>Data inicial</label>
            <input type="date" name="data_inicio" value="{{ $dataInicio }}">
        </div>
        <div class="form-group">
            <label>Data final</label>
            <input type="date" name="data_fim" value="{{ $dataFim }}">
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="ti ti-filter"></i> Aplicar filtro
        </button>
        <a href="{{ route('relatorios.index') }}" class="btn btn-secondary">
            <i class="ti ti-refresh"></i> Hoje
        </a>
        <div class="flex gap-2" style="margin-left:auto">
            <a href="{{ route('relatorios.index', ['data_inicio' => now()->subDays(6)->toDateString(), 'data_fim' => $hoje]) }}"
               class="btn btn-secondary btn-sm">7 dias</a>
            <a href="{{ route('relatorios.index', ['data_inicio' => now()->subDays(29)->toDateString(), 'data_fim' => $hoje]) }}"
               class="btn btn-secondary btn-sm">30 dias</a>
            <a href="{{ route('relatorios.index', ['data_inicio' => now()->startOfMonth()->toDateString(), 'data_fim' => $hoje]) }}"
               class="btn btn-secondary btn-sm">Este mês</a>
        </div>
    </form>
    <div class="text-muted" style="margin-top:0.75rem">
        <i class="ti ti-info-circle"></i>
        Exibindo dados de <strong>{{ $periodoLabel }}</strong>
        <span style="margin-left:6px">(carteiras via <code>vw_carteiras_saldo</code> — momento atual)</span>
    </div>
</div>

<div class="card">
    <div class="card-title">
        <i class="ti ti-tools-kitchen-2"></i>
        Refeições por categoria — {{ $periodoLabel }}
        <span class="text-muted" style="font-weight:400;margin-left:8px">(<code>vw_refeicoes_por_categoria</code>)</span>
    </div>
    @forelse ($refeicoesCat as $r)
        <div class="report-row">
            <span class="label">{{ $r->categoria }}</span>
            <span class="value">{{ $r->qtd }} refeição(ões) &nbsp;·&nbsp; R$ {{ number_format((float) $r->total, 2, ',', '.') }}</span>
        </div>
    @empty
        <p class="text-muted">Nenhuma refeição registrada no período selecionado.</p>
    @endforelse
    @if (count($refeicoesCat) > 0)
        <div class="report-row total">
            <span class="label">Total arrecadado no período ({{ $qtdRefeicoes }} refeições)</span>
            <span class="value">R$ {{ number_format((float) $totalPeriodo, 2, ',', '.') }}</span>
        </div>
    @endif
</div>

<div class="card">
    <div class="card-title">
        <i class="ti ti-file-invoice"></i>
        Contas a pagar pendentes — vencimento no período
        <span class="text-muted" style="font-weight:400;margin-left:8px">(<code>vw_contas_pagar_pendentes</code>)</span>
    </div>
    @if (count($contasPagar) === 0)
        <p class="text-muted">Nenhuma conta pendente com vencimento no período selecionado.</p>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Origem</th>
                        <th>Fornecedor</th>
                        <th>Valor</th>
                        <th>Vencimento</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($contasPagar as $c)
                        <tr>
                            <td>{{ $c->origem }}</td>
                            <td>{{ $c->razao_social ?? '—' }}</td>
                            <td>R$ {{ number_format((float) $c->valor, 2, ',', '.') }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($c->data_vencimento)->format('d/m/Y') }}</td>
                            <td><span class="pill pill-amber">{{ $c->status }}</span></td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="2"><strong>Total pendente no período</strong></td>
                        <td><strong>R$ {{ number_format((float) $totalPendente, 2, ',', '.') }}</strong></td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="card">
    <div class="card-title">
        <i class="ti ti-wallet"></i>
        Saldo das carteiras digitais
        <span class="text-muted">(momento atual · <code>vw_carteiras_saldo</code>)</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>Categoria</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($carteiras as $c)
                    <tr>
                        <td>{{ $c->nome_completo }}</td>
                        <td>{{ $c->categoria }}</td>
                        <td>R$ {{ number_format((float) $c->saldo, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-title"><i class="ti ti-cash"></i> Contas a receber — previstas no período</div>
    @forelse ($contasReceber as $r)
        <div class="report-row">
            <span class="label">{{ $r->origem }} — {{ \Illuminate\Support\Carbon::parse($r->data_prevista)->format('d/m/Y') }}</span>
            <span class="value">R$ {{ number_format((float) $r->valor, 2, ',', '.') }}</span>
        </div>
    @empty
        <p class="text-muted">Nenhuma conta a receber prevista no período selecionado.</p>
    @endforelse
    @if (count($contasReceber) > 0)
        <div class="report-row total">
            <span class="label">Total previsto no período</span>
            <span class="value">R$ {{ number_format((float) $totalReceber, 2, ',', '.') }}</span>
        </div>
    @endif
</div>

<div class="card">
    <div class="card-title"><i class="ti ti-arrows-exchange"></i> Movimentações de estoque — {{ $periodoLabel }}</div>
    @if (count($movimentos) === 0)
        <p class="text-muted">Nenhuma movimentação de estoque no período selecionado.</p>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Tipo</th>
                        <th>Qtd</th>
                        <th>Data</th>
                        <th>Fornecedor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($movimentos as $m)
                        <tr>
                            <td>{{ $m->nome_produto }}</td>
                            <td>
                                <span class="pill {{ $m->tipo_movimento === 'ENTRADA' ? 'pill-green' : 'pill-red' }}">
                                    {{ $m->tipo_movimento }}
                                </span>
                            </td>
                            <td>{{ $m->quantidade_mov }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($m->data_movimento)->format('d/m/Y H:i') }}</td>
                            <td>{{ $m->razao_social ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
