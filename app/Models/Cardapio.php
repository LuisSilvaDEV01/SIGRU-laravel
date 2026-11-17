<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cardapio extends Model
{
    protected $table = 'cardapio';

    protected $primaryKey = 'id_cardapio';

    public $timestamps = false;

    protected $fillable = [
        'data_servico',
        'turno',
    ];

    protected $casts = [
        'data_servico' => 'date',
    ];

    public function itens(): HasMany
    {
        return $this->hasMany(ItensCardapio::class, 'id_cardapio', 'id_cardapio');
    }
}
