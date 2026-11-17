<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    protected $table = 'fornecedores';

    protected $primaryKey = 'cnpj';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'cnpj',
        'razao_social',
        'telefone',
        'id_bairro',
        'endereco',
    ];
}
