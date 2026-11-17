@extends('layouts.app')

@section('content')
<div class="card">
    <div class="flex gap-2" style="margin-bottom:1.25rem;border-bottom:1px solid var(--border);padding-bottom:0">
        <button type="button" class="tab-btn active" data-tab="tab-produto"
                style="padding:8px 4px;margin-right:1.5rem;border:none;background:none;cursor:pointer;
                       font-size:13px;font-weight:500;color:var(--purple-600);border-bottom:2px solid var(--purple-600)">
            <i class="ti ti-box"></i> Cadastrar mercadoria
        </button>
        <button type="button" class="tab-btn" data-tab="tab-movimento"
                style="padding:8px 4px;border:none;background:none;cursor:pointer;
                       font-size:13px;font-weight:500;color:var(--text-muted);border-bottom:2px solid transparent">
            <i class="ti ti-arrows-exchange"></i> Registrar movimentação
        </button>
    </div>

    <div id="tab-produto" class="tab-content">
        <form method="POST" action="{{ route('estoque.produto') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label>Nome da mercadoria *</label>
                    <input type="text" name="nome_produto" placeholder="Ex: Óleo de Soja 900ml" value="{{ old('nome_produto') }}" required>
                </div>
                <div class="form-group">
                    <label>Categoria *</label>
                    <select name="id_tipo_produto" required>
                        <option value="">Selecione...</option>
                        @foreach ($tiposProduto as $t)
                            <option value="{{ $t->id_tipo }}" @selected(old('id_tipo_produto') == $t->id_tipo)>
                                {{ $t->descricao_tipo }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Unidade de medida *</label>
                    <select name="unidade_medida" required>
                        @foreach (['KG','LT','UN','CX','PT'] as $u)
                            <option value="{{ $u }}" @selected(old('unidade_medida', 'KG') === $u)>{{ $u }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantidade inicial</label>
                    <input type="number" name="quantidade_inicial" min="0" placeholder="0 (opcional)" value="{{ old('quantidade_inicial') }}">
                </div>
            </div>
            <div style="margin-top:1rem">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy"></i> Cadastrar mercadoria
                </button>
            </div>
        </form>
    </div>

    <div id="tab-movimento" class="tab-content" style="display:none">
        <form method="POST" action="{{ route('estoque.movimentar') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label>Produto *</label>
                    <select name="id_produto" required>
                        <option value="">Selecione o produto...</option>
                        @foreach ($produtos as $p)
                            <option value="{{ $p->id_produto }}" @selected(old('id_produto') == $p->id_produto)>
                                {{ $p->nome_produto }} ({{ $p->unidade_medida }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Tipo de movimentação *</label>
                    <select name="tipo_movimento" id="tipo_movimento" required>
                        <option value="">Selecione...</option>
                        <option value="ENTRADA" @selected(old('tipo_movimento') === 'ENTRADA')>↑ Entrada</option>
                        <option value="SAIDA" @selected(old('tipo_movimento') === 'SAIDA')>↓ Saída</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantidade *</label>
                    <input type="number" name="quantidade_mov" min="1" placeholder="Ex: 50" value="{{ old('quantidade_mov') }}" required>
                </div>
                <div class="form-group" id="campo-fornecedor">
                    <label>Fornecedor <span class="text-muted">(opcional)</span></label>
                    <select name="cnpj_fornecedor">
                        <option value="">— Nenhum / não se aplica —</option>
                        @foreach ($fornecedores as $f)
                            <option value="{{ $f->cnpj }}" @selected(old('cnpj_fornecedor') == $f->cnpj)>
                                {{ $f->razao_social }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="margin-top:1rem">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy"></i> Registrar movimentação
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-title"><i class="ti ti-package"></i> Estoque atual por produto</div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Categoria</th>
                    <th>Quantidade</th>
                    <th>Unidade</th>
                    <th>Última atualização</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($estoque as $e)
                    <tr>
                        <td><strong>{{ $e->nome_produto }}</strong></td>
                        <td>{{ $e->descricao_tipo }}</td>
                        <td style="font-size:15px;font-weight:600">{{ $e->quantidade }}</td>
                        <td>{{ $e->unidade_medida }}</td>
                        <td class="text-muted">
                            {{ $e->data_atualizacao ? \Illuminate\Support\Carbon::parse($e->data_atualizacao)->format('d/m/Y H:i') : '—' }}
                        </td>
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

<div class="card">
    <div class="card-title"><i class="ti ti-history"></i> Histórico de movimentações recentes</div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Data / Hora</th>
                    <th>Produto</th>
                    <th>Tipo</th>
                    <th>Quantidade</th>
                    <th>Fornecedor</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($historico as $h)
                    <tr>
                        <td class="text-muted">{{ \Illuminate\Support\Carbon::parse($h->data_movimento)->format('d/m/Y H:i') }}</td>
                        <td>{{ $h->nome_produto }}</td>
                        <td>
                            @if ($h->tipo_movimento === 'ENTRADA')
                                <span class="pill pill-green">↑ Entrada</span>
                            @else
                                <span class="pill pill-red">↓ Saída</span>
                            @endif
                        </td>
                        <td><strong>{{ $h->quantidade_mov }}</strong> {{ $h->unidade_medida }}</td>
                        <td>{{ $h->razao_social ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.style.color = 'var(--text-muted)';
            b.style.borderBottomColor = 'transparent';
        });
        document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
        btn.style.color = 'var(--purple-600)';
        btn.style.borderBottomColor = 'var(--purple-600)';
        document.getElementById(btn.dataset.tab).style.display = 'block';
    });
});
</script>
@endpush
