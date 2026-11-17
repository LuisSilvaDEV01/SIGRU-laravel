<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItensCardapioTipo extends Model
{
    protected $table = 'itens_cardapio_tipo';

    protected $primaryKey = 'id_tipo';

    public $timestamps = false;

    protected $fillable = [
        'descricao',
    ];

    public function itens(): HasMany
    {
        return $this->hasMany(ItensCardapio::class, 'id_tipo', 'id_tipo');
    }
}
