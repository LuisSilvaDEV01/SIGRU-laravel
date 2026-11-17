@extends('layouts.app')

@section('content')
<div class="metric-grid">
    <div class="metric-card accent">
        <div class="metric-label">Usuários cadastrados</div>
        <div class="metric-value">{{ $dash->total_usuarios ?? 0 }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Refeições hoje</div>
        <div class="metric-value">{{ $dash->refeicoes_hoje ?? 0 }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Em fila agora</div>
        <div class="metric-value">{{ $dash->fila_espera ?? 0 }}</div>
    </div>
    <div class="metric-card accent">
        <div class="metric-label">Caixa do dia</div>
        <div class="metric-value">R$ {{ number_format((float) ($dash->caixa_hoje ?? 0), 2, ',', '.') }}</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem">
    <div class="card" style="margin-bottom:0">
        <div class="card-title"><i class="ti ti-clipboard-list"></i> Cardápio de hoje</div>
        @if ($cardapioAgrupado->isEmpty())
            <p class="text-muted">Nenhum cardápio cadastrado para hoje.</p>
        @else
            @foreach ($cardapioAgrupado as $turno => $itens)
                <div style="margin-bottom:1rem">
                    <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;
                                color:var(--text-muted);margin-bottom:8px;padding-bottom:6px;
                                border-bottom:1px solid var(--border)">
                        <i class="ti {{ $turno === 'Almoço' ? 'ti-sun' : 'ti-moon' }}"></i>
                        {{ $turno }}
                    </div>
                    @foreach ($itens as $item)
                        <div style="display:flex;align-items:flex-start;gap:8px;padding:6px 0;border-bottom:1px solid var(--border)">
                            <span class="pill pill-purple" style="flex-shrink:0;margin-top:1px">{{ $item->tipo_item }}</span>
                            @if ($item->detalhe)
                                <span style="font-size:12px;color:var(--text-muted);line-height:1.5">{{ $item->detalhe }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif
    </div>

    <div class="card" style="margin-bottom:0">
        <div class="card-title"><i class="ti ti-package"></i> Estoque atual</div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Produto</th><th>Qtd.</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach ($estoque as $e)
                        <tr>
                            <td>{{ $e->nome_produto }}</td>
                            <td>{{ $e->quantidade }} {{ $e->unidade_medida }}</td>
                            <td>
                                @if ($e->status_estoque === 'Zerado')
                                    <span class="pill pill-red">Zerado</span>
                                @elseif ($e->status_estoque === 'Baixo')
                                    <span class="pill pill-amber">Baixo</span>
                                @else
                                    <span class="pill pill-green">OK</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card" style="margin-top:1.25rem">
    <div class="card-title"><i class="ti ti-tools-kitchen-2"></i> Últimas refeições</div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>Cardápio</th>
                    <th>Valor</th>
                    <th>Entrada</th>
                    <th>Saída</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ultimasRefeicoes as $r)
                    <tr>
                        <td>{{ $r->nome_completo }}</td>
                        <td>{{ $r->turno }} — {{ \Illuminate\Support\Carbon::parse($r->data_servico)->format('d/m') }}</td>
                        <td>R$ {{ number_format((float) $r->valor, 2, ',', '.') }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($r->horario_entrada)->format('H:i') }}</td>
                        <td>
                            @if ($r->horario_saida)
                                {{ \Illuminate\Support\Carbon::parse($r->horario_saida)->format('H:i') }}
                            @else
                                <span class="pill pill-amber">Em curso</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
