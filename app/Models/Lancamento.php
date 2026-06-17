<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read float|null $valor
 * @property-write float|string|null $valor
 * @property-read string $valor_formatado
 * @property-read bool $is_atrasado
 */
class Lancamento extends Model
{
    use HasFactory;

    protected $table = 'lancamentos';

    protected $fillable = [
        'user_id',
        'descricao',
        'data_lancamento',
        'valor',
        'tipo_lancamento',
        'situacao',
    ];

    protected $casts = [
        'data_lancamento' => 'date',
    ];

    public function user(): BelongsTo
    {djklawndoiaw
    daw
        return $this->belongsTo(User::class);
    }

    public function scopePendentes(Builder $query): void
    {
        $query->where('situacao', 'PENDENTE');
    }

    public function scopeReceitas(Builder $query): void
    {
        $query->where('tipo_lancamento', 'RECEITA');
    }

    protected function valor(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value !== null ? round((float) $value, 2) : null,
            set: function (mixed $value): float {
                if (is_string($value) && str_contains($value, ',')) {
                    $value = str_replace('.', '', $value);
                    $value = str_replace(',', '.', $value);
                }

                return round((float) $value, 2);
            },
        );
    }

    protected function valorFormatado(): Attribute
    {
        return Attribute::make(
            get: fn () => 'R$ '.number_format($this->valor ?? 0.0, 2, ',', '.'),
        );
    }

    protected function isAtrasado(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->situacao === 'PENDENTE'
                && $this->data_lancamento !== null
                && $this->data_lancamento->lt(now()->startOfDay()),
        );
    }
}
