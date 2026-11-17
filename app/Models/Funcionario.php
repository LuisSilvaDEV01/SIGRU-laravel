<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    protected $table = 'funcionario';

    protected $primaryKey = 'cpf';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'cpf',
        'nome_funcionario',
        'cargo',
        'privilegios',
        'id_bairro',
        'endereco',
    ];
}
