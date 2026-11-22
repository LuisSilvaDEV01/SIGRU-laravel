<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cidade extends Model
{
    protected $table = 'cidade';

    protected $primaryKey = 'id_cidade';

    public $timestamps = false;

    protected $fillable = [
        'descricao',
        'uf',
        'codigo_ibge',
    ];

    public function bairros(): HasMany
    {
        return $this->hasMany(Bairro::class, 'id_cidade', 'id_cidade');
    }
}
