<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fila extends Model
{
    protected $table = 'fila';

    protected $primaryKey = 'id_fila';

    public $timestamps = false;

    protected $fillable = [
        'tipo_fila',
    ];

    public function itens(): HasMany
    {
        return $this->hasMany(ItensFila::class, 'id_fila', 'id_fila');
    }
}
