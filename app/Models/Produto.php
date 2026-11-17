<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Produto extends Model
{
    protected $table = 'produto';

    protected $primaryKey = 'id_produto';

    public $timestamps = false;

    protected $fillable = [
        'nome_produto',
        'unidade_medida',
        'id_tipo_produto',
    ];

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoProduto::class, 'id_tipo_produto', 'id_tipo');
    }

    public function estoque(): HasOne
    {
        return $this->hasOne(Estoque::class, 'id_produto', 'id_produto');
    }
}
