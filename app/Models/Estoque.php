<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Estoque extends Model
{
    protected $table = 'estoque';

    protected $primaryKey = 'id_estoque';

    public $timestamps = false;

    protected $fillable = [
        'quantidade',
        'data_atualizacao',
        'id_produto',
    ];

    protected $casts = [
        'data_atualizacao' => 'datetime',
    ];

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'id_produto', 'id_produto');
    }
}
