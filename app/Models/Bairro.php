<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bairro extends Model
{
    protected $table = 'bairro';

    protected $primaryKey = 'id_bairro';

    public $timestamps = false;

    protected $fillable = [
        'id_cidade',
        'descricao',
    ];

    public function cidade(): BelongsTo
    {
        return $this->belongsTo(Cidade::class, 'id_cidade', 'id_cidade');
    }
}
