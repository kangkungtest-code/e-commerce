<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'gateway', 'status', 'mata_uang', 'jumlah', 'transaksi_id_eksternal', 'raw_payload'])]
class Payment extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return ['jumlah' => 'decimal:2', 'raw_payload' => 'array'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
