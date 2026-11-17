<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContaReceber extends Model
{
    protected $table = 'conta_receber';

    protected $primaryKey = 'id_conta_receber';

    public $timestamps = false;

    protected $fillable = [
        'valor',
        'data_prevista',
        'origem',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_prevista' => 'date',
    ];
}
