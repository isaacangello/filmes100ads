<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Histórico de saques de rendimento.
 */
class Withdrawal extends Model
{
    protected $table = 'withdrawals';

    protected $fillable = [
        'user_id',
        'amount_mint',
        'amount_btc',
        'status',
        'btc_address',
    ];

    protected $casts = [
        'amount_mint' => 'decimal:2',
        'amount_btc' => 'decimal:8',
    ];

    /**
     * Usuário que solicitou o saque.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica se o saque está pendente.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Verifica se o saque foi concluído.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Verifica se o saque foi rejeitado.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
