<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lancamento extends Model
{
    use HasFactory;

    protected $table = 'lancamentos';

    protected $fillable = [
        'descricao',
        'data_lancamento',
        'valor',
        'tipo_lancamento',
        'situacao',
    ];

    protected $casts = [
        'data_lancamento' => 'date',
        'valor' => 'decimal:2',
    ];
}
