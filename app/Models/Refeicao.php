<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refeicao extends Model
{
    protected $table = 'refeicao';

    protected $primaryKey = 'id_refeicao';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_itens_cardapio',
        'id_categoria_valores',
        'valor',
        'horario_entrada',
        'horario_saida',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'horario_entrada' => 'datetime',
        'horario_saida' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function itemCardapio(): BelongsTo
    {
        return $this->belongsTo(ItensCardapio::class, 'id_itens_cardapio', 'id_itens_cardapio');
    }
}
