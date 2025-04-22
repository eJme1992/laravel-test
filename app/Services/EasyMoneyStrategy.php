<?php

namespace App\Services;

use App\Constants\PaymentMessages;
use App\Constants\ResponseStatus;
use App\DTOs\ProviderResponseDto;
use App\Interfaces\PaymentStrategyInterface;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EasyMoneyStrategy implements PaymentStrategyInterface
{
    public function pay(array $data): ?ProviderResponseDto
    {
        $requiredFields = ['provider_id', 'amount', 'currency', 'base_url'];

        foreach ($requiredFields as $field) {
            if (! array_key_exists($field, $data)) {
                return $this->failEarly(null, sprintf(PaymentMessages::MISSING_FIELD, $field));
            }
        }

        $transaction = Transaction::create([
            'payment_provider_id' => $data['provider_id'],
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'status' => ResponseStatus::PENDING,
        ]);

        $requestData = [
            'amount' => (int) $data['amount'],
            'currency' => $data['currency'],
        ];

        $responseDto = new ProviderResponseDto('easymoney', $requestData);

        if ($this->isInvalidAmount($data['amount'])) {
            $responseDto->setErrorMessage(PaymentMessages::INVALID_AMOUNT);
            $responseDto->setAttemptToConnect(false);
            $responseDto->setTransaction(
                $this->failEarly($transaction, PaymentMessages::INVALID_AMOUNT)
            );

            return $responseDto;
        }

        $responseDto->setAttemptToConnect(true);

        try {
            $response = Http::post(
                rtrim($data['base_url'], '/').'/process',
                $requestData
            );

            if ($response->successful()) {
                $transaction->update([
                    'status' => ResponseStatus::SUCCESS,
                    'external_transaction_id' => $response['transaction_id'] ?? null,
                ]);
            } else {
                $transaction->update([
                    'status' => ResponseStatus::FAILED,
                    'error_message' => $response->body(),
                ]);
            }

            $responseDto->setStatus($response->getStatusCode());
            $responseDto->setResponseData($response->body());

        } catch (\Throwable $e) {
            Log::error(PaymentMessages::REQUEST_FAILED.$e->getMessage());

            $transaction->update([
                'status' => ResponseStatus::FAILED,
                'error_message' => $e->getMessage(),
            ]);

            $responseDto->setStatus(ResponseStatus::HTTP_INTERNAL_ERROR);
            $responseDto->setResponseData([
                'error_message' => PaymentMessages::REQUEST_FAILED,
            ]);
        }

        $responseDto->setTransaction($transaction);

        return $responseDto;
    }

    private function isInvalidAmount($amount): bool
    {
        return is_numeric($amount) && preg_match('/^-?\d+\.\d+$/', $amount);
    }

    private function failEarly(?Transaction $transaction, string $message)
    {
        if (! $transaction) {
            return (object) [
                'status' => ResponseStatus::FAILED,
                'error_message' => $message,
            ];
        }

        $transaction->update([
            'status' => ResponseStatus::FAILED,
            'error_message' => $message,
        ]);

        return $transaction;
    }
}
