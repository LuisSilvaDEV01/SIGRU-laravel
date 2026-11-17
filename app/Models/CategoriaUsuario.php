<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaUsuario extends Model
{
    protected $table = 'categoria_usuario';

    protected $primaryKey = 'id_categoria';

    public $timestamps = false;

    protected $fillable = [
        'descricao',
    ];

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'id_categoria', 'id_categoria');
    }
}
