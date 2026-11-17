<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Usuario extends Model
{
    protected $table = 'usuario';

    protected $primaryKey = 'id_usuario';

    public $timestamps = false;

    protected $fillable = [
        'matricula',
        'nome_completo',
        'id_categoria',
        'id_bairro',
        'endereco',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaUsuario::class, 'id_categoria', 'id_categoria');
    }

    public function carteira(): HasOne
    {
        return $this->hasOne(CarteiraDigital::class, 'id_usuario', 'id_usuario');
    }

    public function itensFila(): HasMany
    {
        return $this->hasMany(ItensFila::class, 'id_usuario', 'id_usuario');
    }

    public function refeicoes(): HasMany
    {
        return $this->hasMany(Refeicao::class, 'id_usuario', 'id_usuario');
    }
}
