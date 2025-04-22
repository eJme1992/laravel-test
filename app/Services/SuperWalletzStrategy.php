<?php

namespace App\Services;

use App\Constants\PaymentMessages;
use App\Constants\ResponseStatus;
use App\DTOs\ProviderResponseDto;
use App\Interfaces\PaymentStrategyInterface;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SuperWalletzStrategy implements PaymentStrategyInterface
{
    public function pay(array $data): ?ProviderResponseDto
    {
        $requiredFields = ['provider_id', 'amount', 'currency', 'base_url', 'callback_url'];
        foreach ($requiredFields as $field) {
            if (! isset($data[$field])) {
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
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'callback_url' => $data['callback_url'],
        ];

        $responseDto = new ProviderResponseDto('superwalletz', $requestData);
        $responseDto->setAttemptToConnect(true);

        try {
            $response = Http::post(
                rtrim($data['base_url'], '/').'/pay',
                $requestData
            );

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['transaction_id'])) {
                $transaction->update([
                    'external_transaction_id' => $responseData['transaction_id'],
                    // No se actualiza a 'success' porque el webhook lo hará más adelante
                ]);
            } else {
                $transaction->update([
                    'status' => ResponseStatus::FAILED,
                    'error_message' => $response->body(),
                ]);
            }

            $responseDto->setStatus($response->status());
            $responseDto->setResponseData($responseData);
            $responseDto->setErrorMessage($responseData['error_message'] ?? null);

        } catch (\Throwable $e) {
            Log::error(PaymentMessages::REQUEST_FAILED.$e->getMessage());

            $transaction->update([
                'status' => ResponseStatus::FAILED,
                'error_message' => $e->getMessage(),
            ]);

            $responseDto->setAttemptToConnect(false);
            $responseDto->setStatus(ResponseStatus::HTTP_INTERNAL_ERROR);
            $responseDto->setResponseData([
                'error_message' => PaymentMessages::REQUEST_FAILED,
            ]);
            $responseDto->setErrorMessage($e->getMessage());
        }

        $responseDto->setTransaction($transaction);

        return $responseDto;
    }

    private function failEarly(?Transaction $transaction, string $message): ProviderResponseDto
    {
        $responseDto = new ProviderResponseDto('superwalletz', []);
        $responseDto->setAttemptToConnect(false);
        $responseDto->setErrorMessage($message);
        $responseDto->setStatus(ResponseStatus::FAILED);

        if ($transaction) {
            $transaction->update([
                'status' => ResponseStatus::FAILED,
                'error_message' => $message,
            ]);
            $responseDto->setTransaction($transaction);
        } else {
            $responseDto->setTransaction((object) [
                'status' => ResponseStatus::FAILED,
                'error_message' => $message,
            ]);
        }

        return $responseDto;
    }
}
