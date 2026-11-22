@extends('layouts.app')

@section('content')
@php
    $isEdit = $usuario->exists;
@endphp

@if ($errors->any())
    <div class="alert alert-error">
        <i class="ti ti-alert-circle"></i>
        {{ $errors->first() }}
    </div>
@endif

<div class="card">
    <div class="card-title">
        <i class="ti {{ $isEdit ? 'ti-edit' : 'ti-user-plus' }}"></i>
        {{ $isEdit ? 'Editar usuário' : 'Novo usuário' }}
    </div>
    <form method="POST"
          action="{{ $isEdit ? route('usuarios.update', $usuario->id_usuario) : route('usuarios.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif
        <div class="form-grid">
            <div class="form-group">
                <label>Nome completo *</label>
                <input type="text" name="nome_completo" placeholder="Ex: João da Silva"
                       value="{{ old('nome_completo', $usuario->nome_completo) }}" required>
            </div>
            <div class="form-group">
                <label>Matrícula / SIAPE *</label>
                <input type="number" name="matricula" placeholder="Ex: 202410099"
                       value="{{ old('matricula', $usuario->matricula) }}" required>
            </div>
            <div class="form-group">
                <label>Categoria *</label>
                <select name="id_categoria" required>
                    <option value="">Selecione...</option>
                    @foreach ($categorias as $c)
                        <option value="{{ $c->id_categoria }}"
                            @selected(old('id_categoria', $usuario->id_categoria) == $c->id_categoria)>
                            {{ $c->descricao }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Bairro *</label>
                <select name="id_bairro" required>
                    <option value="">Selecione...</option>
                    @foreach ($bairros as $b)
                        <option value="{{ $b->id_bairro }}"
                            @selected(old('id_bairro', $usuario->id_bairro) == $b->id_bairro)>
                            {{ $b->descricao }} — {{ $b->cidade->descricao ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group full">
                <label>Endereço *</label>
                <input type="text" name="endereco" placeholder="Rua, número, complemento"
                       value="{{ old('endereco', $usuario->endereco) }}" required>
            </div>
        </div>
        <div class="flex gap-2" style="margin-top:1.25rem">
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy"></i>
                {{ $isEdit ? 'Salvar alterações' : 'Salvar usuário' }}
            </button>
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
