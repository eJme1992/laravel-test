<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_provider_id',
        'amount',
        'currency',
        'external_transaction_id',
        'status',
        'error_message',
    ];

    public function provider()
    {
        return $this->belongsTo(PaymentProvider::class, 'payment_provider_id');
    }

    public function requestLogs()
    {
        return $this->hasMany(PaymentRequestLog::class);
    }

    public function webhookLogs()
    {
        return $this->hasMany(WebhookLog::class);
    }
}
