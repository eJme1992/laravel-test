<?php

namespace App\DTOs;

use App\Models\Transaction;

class ProviderResponseDto
{
    public string $provider;

    public $requestData;

    public $responseData;

    public ?string $status;

    public ?Transaction $transaction;

    public ?string $errorMessage;

    public bool $conection;

    public function __construct(string $provider, array $requestData)
    {
        $this->provider = $provider;
        $this->requestData = $requestData;
        $this->conection = false;
    }

    public function setResponseData($responseData): void
    {
        $this->responseData = $responseData;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function setTransaction(Transaction $transaction): void
    {
        $this->transaction = $transaction;
    }

    public function setErrorMessage(?string $errorMessage): void
    {
        $this->responseData['error_message'] = $errorMessage;
    }

    public function setAttemptToConnect(bool $conection): void
    {
        $this->conection = $conection;
    }

    public function getAttemptToConnect(): bool
    {
        return $this->conection;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['provider'],
            $data['requestData'],
            $data['responseData'],
            $data['status'] ?? null
        );
    }

    public function getErrorMessage(): ?string
    {
        return $this->responseData['error_message'] ?? null;
    }

    public function getRequestData()
    {
        return $this->requestData;
    }

    public function getResponseData()
    {
        return $this->responseData;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'request_data' => $this->requestData,
            'response_data' => $this->responseData,
            'status' => $this->status,
        ];
    }
}
