<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarteiraDigital extends Model
{
    protected $table = 'carteira_digital';

    protected $primaryKey = 'id_carteira';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'saldo',
    ];

    protected $casts = [
        'saldo' => 'decimal:2',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function recargas(): HasMany
    {
        return $this->hasMany(RecargaHistorico::class, 'id_carteira', 'id_carteira');
    }
}
