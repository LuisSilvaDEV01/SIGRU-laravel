@extends('layouts.app')

@section('content')
@if (!empty($sucesso) && $sucesso)
    <div class="alert alert-success">
        <i class="ti ti-check"></i>
        Refeição registrada com sucesso! Carteira debitada via trigger e fila finalizada via procedure.
        <a href="{{ route('fila.index') }}" style="margin-left:8px;font-weight:500">Voltar à fila</a>
    </div>
@endif

@if (!empty($erro))
    <div class="alert alert-error"><i class="ti ti-alert-circle"></i> {{ $erro }}</div>
@endif

<div class="card">
    <div class="card-title"><i class="ti ti-user"></i> Usuário em atendimento</div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem">
        <div>
            <div class="text-muted">Nome</div>
            <strong>{{ $item->nome_completo }}</strong>
        </div>
        <div>
            <div class="text-muted">Matrícula</div>
            <strong>{{ $item->matricula }}</strong>
        </div>
        <div>
            <div class="text-muted">Categoria</div>
            <strong>{{ $item->categoria }}</strong>
        </div>
        <div>
            <div class="text-muted">Tipo de fila</div>
            <strong>{{ $item->tipo_fila }}</strong>
        </div>
        <div>
            <div class="text-muted">Saldo na carteira</div>
            <strong style="color:{{ (float) $item->saldo < 10 ? '#A32D2D' : '#27500A' }}">
                R$ {{ number_format((float) $item->saldo, 2, ',', '.') }}
            </strong>
        </div>
    </div>
</div>

@if (empty($sucesso))
<div class="card">
    <div class="card-title"><i class="ti ti-tools-kitchen-2"></i> Selecione o item do cardápio</div>

    @if (count($cardapios) === 0)
        <div class="alert alert-error">
            <i class="ti ti-alert-circle"></i>
            Nenhum cardápio cadastrado para hoje.
        </div>
    @else
        <form method="POST" action="{{ route('refeicao.store') }}" id="form-refeicao">
            @csrf
            <input type="hidden" name="id_fila" value="{{ $item->id_itens_fila }}">
            <input type="hidden" name="id_itens_cardapio" id="campo_id_itens_cardapio">
            <input type="hidden" name="id_categoria_valores" id="campo_id_categoria_valores">
            <input type="hidden" name="valor" id="campo_valor">

            <div style="display:grid;gap:0.75rem;margin-bottom:1.25rem">
                @php $turnoAtual = ''; @endphp
                @foreach ($cardapios as $c)
                    @if ($c->turno !== $turnoAtual)
                        @php $turnoAtual = $c->turno; @endphp
                        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;
                                    color:var(--text-muted);margin-top:0.5rem;padding-bottom:4px;
                                    border-bottom:1px solid var(--border)">
                            {{ $turnoAtual }}
                        </div>
                    @endif

                    <label class="opcao-refeicao"
                           data-id="{{ $c->id_itens_cardapio }}"
                           data-valor="{{ $c->valor }}"
                           data-catval="{{ $c->id_categoria_valores }}"
                           data-label="{{ $c->tipo_item }}"
                           style="display:flex;align-items:center;justify-content:space-between;
                                  padding:12px 16px;border:1.5px solid var(--border);border-radius:var(--radius-md);
                                  cursor:pointer;transition:border-color 0.15s,background 0.15s">
                        <div>
                            <div style="font-weight:500;font-size:14px">{{ $c->tipo_item }}</div>
                            @if ($c->descricao_item)
                                <div class="text-muted" style="margin-top:2px">{{ $c->descricao_item }}</div>
                            @endif
                        </div>
                        <div style="font-size:16px;font-weight:600;color:var(--purple-600)">
                            @if ((float) $c->valor > 0)
                                R$ {{ number_format((float) $c->valor, 2, ',', '.') }}
                            @else
                                <span class="pill pill-amber">Sem preço</span>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>

            <div id="resumo" style="display:none;background:var(--purple-50);border:1.5px solid var(--purple-200);
                                     border-radius:var(--radius-md);padding:1rem 1.25rem;margin-bottom:1rem">
                <div style="font-size:13px;color:var(--purple-800)">Item selecionado</div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px">
                    <strong id="resumo-item" style="font-size:15px"></strong>
                    <strong id="resumo-valor" style="font-size:18px;color:var(--purple-600)"></strong>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" id="btn-confirmar" class="btn btn-primary" disabled>
                    <i class="ti ti-check"></i> Confirmar refeição
                </button>
                <a href="{{ route('fila.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    @endif
</div>
@endif
@endsection

@push('scripts')
<script>
document.querySelectorAll('.opcao-refeicao').forEach(el => {
    el.addEventListener('click', () => {
        document.querySelectorAll('.opcao-refeicao').forEach(o => {
            o.style.borderColor = 'var(--border)';
            o.style.background = '';
        });
        el.style.borderColor = 'var(--purple-600)';
        el.style.background = 'var(--purple-50)';

        const valor = parseFloat(el.dataset.valor);
        const catVal = el.dataset.catval;

        document.getElementById('campo_id_itens_cardapio').value = el.dataset.id;
        document.getElementById('campo_valor').value = valor;
        document.getElementById('campo_id_categoria_valores').value = catVal || 0;

        document.getElementById('resumo').style.display = 'block';
        document.getElementById('resumo-item').textContent = el.dataset.label;
        document.getElementById('resumo-valor').textContent =
            'R$ ' + valor.toFixed(2).replace('.', ',');

        document.getElementById('btn-confirmar').disabled = !(valor > 0 && catVal);
    });
});
</script>
@endpush
