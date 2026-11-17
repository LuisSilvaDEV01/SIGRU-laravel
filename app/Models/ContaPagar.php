<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContaPagar extends Model
{
    protected $table = 'conta_pagar';

    protected $primaryKey = 'id_conta_pagar';

    public $timestamps = false;

    protected $fillable = [
        'valor',
        'data_vencimento',
        'status',
        'origem',
        'cnpj_fornecedor',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_vencimento' => 'date',
    ];

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class, 'cnpj_fornecedor', 'cnpj');
    }
}
