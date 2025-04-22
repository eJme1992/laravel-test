<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentRequestLog extends Model
{
    use HasFactory;
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'payload',
        'response',
        'status_code',
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
