<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimentoEstoque extends Model
{
    protected $table = 'movimento_estoque';

    protected $primaryKey = 'id_movimento';

    public $timestamps = false;

    protected $fillable = [
        'tipo_movimento',
        'quantidade_mov',
        'data_movimento',
        'id_produto',
        'cnpj_fornecedor',
    ];

    protected $casts = [
        'data_movimento' => 'datetime',
    ];

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'id_produto', 'id_produto');
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class, 'cnpj_fornecedor', 'cnpj');
    }
}
