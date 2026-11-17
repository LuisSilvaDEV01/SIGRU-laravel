<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecargaHistorico extends Model
{
    protected $table = 'recarga_historico';

    protected $primaryKey = 'id_recarga';

    public $timestamps = false;

    protected $fillable = [
        'id_carteira',
        'valor',
        'data',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data' => 'datetime',
    ];

    public function carteira(): BelongsTo
    {
        return $this->belongsTo(CarteiraDigital::class, 'id_carteira', 'id_carteira');
    }
}
