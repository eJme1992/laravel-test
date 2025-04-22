<?php

namespace App\Services;

use App\Interfaces\PaymentStrategyInterface;
use App\Models\PaymentProvider;
use App\Models\PaymentRequestLog;
use App\Models\Transaction;

class PaymentService
{
    public function process(PaymentProvider $provider, array $data): Transaction
    {

       $strategy = $this->resolveStrategy($provider->name);

       $response = $strategy->pay([
        ...$data,
           'provider_id' => $provider->id,
           'base_url' => $provider->base_url,
           'callback_url' => $provider->callback_url,
    ]);

        if ($response->getAttemptToConnect()) {
            $log = new PaymentRequestLog();
            $log->request_body = json_encode($response->getRequestData());
            $log->response_data = json_encode($response->getResponseData());
            $log->save();
        }

       return $response->getTransaction();
    }

    protected function resolveStrategy(string $providerName): PaymentStrategyInterface
    {
        return match ($providerName) {
            'easymoney' => new EasyMoneyStrategy(),
            'superwalletz' => new SuperWalletzStrategy(),
            default => throw new \Exception('Proveedor no soportado'),
        };
    }
}
