<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItensCardapio extends Model
{
    protected $table = 'itens_cardapio';

    protected $primaryKey = 'id_itens_cardapio';

    public $timestamps = false;

    protected $fillable = [
        'id_cardapio',
        'id_tipo',
        'descricao',
    ];

    public function cardapio(): BelongsTo
    {
        return $this->belongsTo(Cardapio::class, 'id_cardapio', 'id_cardapio');
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(ItensCardapioTipo::class, 'id_tipo', 'id_tipo');
    }
}
