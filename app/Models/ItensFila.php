<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItensFila extends Model
{
    protected $table = 'itens_fila';

    protected $primaryKey = 'id_itens_fila';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_fila',
        'horario_inscricao',
        'situacao',
    ];

    protected $casts = [
        'horario_inscricao' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function fila(): BelongsTo
    {
        return $this->belongsTo(Fila::class, 'id_fila', 'id_fila');
    }
}
