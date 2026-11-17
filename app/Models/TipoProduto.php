<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoProduto extends Model
{
    protected $table = 'tipo_produto';

    protected $primaryKey = 'id_tipo';

    public $timestamps = false;

    protected $fillable = [
        'descricao_tipo',
    ];

    public function produtos(): HasMany
    {
        return $this->hasMany(Produto::class, 'id_tipo_produto', 'id_tipo');
    }
}
