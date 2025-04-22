<?php

namespace App\Http\Controllers;

use App\Constants\PaymentResponseMessages;
use App\Constants\TransactionStatus;
use App\Models\Transaction;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Webhooks', description: 'Webhook handling operations')]
class WebhookController extends Controller
{
    #[OA\Post(
        path: '/webhooks/superwalletz',
        operationId: 'handleWebhook',
        summary: 'Handle incoming webhook from SuperWalletz',
        tags: ['Webhooks'],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Webhook payload',
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        required: ['transaction_id', 'status'],
                        properties: [
                            new OA\Property(property: 'transaction_id', type: 'string', example: 'ext-txn-12345'),
                            new OA\Property(property: 'status', type: 'integer', enum: [0, 1], example: 1),
                        ],
                    )
                ),
            ]
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Webhook processed successfully or transaction already processed',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'message', type: 'string', example: PaymentResponseMessages::WEBHOOK_PROCESSED),
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Transaction not found',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'message', type: 'string', example: PaymentResponseMessages::TRANSACTION_NOT_FOUND),
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid webhook data',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'message', type: 'string', example: PaymentResponseMessages::INVALID_DATA),
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Error processing webhook',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'message', type: 'string', example: PaymentResponseMessages::INTERNAL_ERROR),
                            new OA\Property(property: 'error', type: 'string', example: 'Error details'),
                            new OA\Property(property: 'details', type: 'string', example: 'Detailed error message'),
                            new OA\Property(property: 'line', type: 'integer', example: 123),
                        ]
                    )
                )
            ),
        ]
    )]
    public function handle(Request $request)
    {
        // Validar contenido del request (estructura esperada)
        $payload = $request->toArray();

        if (empty($payload['transaction_id']) || empty($payload['status'])) {
            return $this->handleInvalidData($payload);
        }

        // Registra la solicitud en el log
        $webhookLog = WebhookLog::create([
            'payload' => $request->getContent(),
        ]);

        try {
            // Validar existencia de la transacción
            $transaction = Transaction::where('external_transaction_id', $payload['transaction_id'])->first();

            if (empty($transaction)) {
                return $this->handleTransactionNotFound($webhookLog);
            }

            // Verificar si la transacción ya fue procesada
            if (in_array($transaction->status, [TransactionStatus::SUCCESS, TransactionStatus::FAILED])) {
                return $this->handleTransactionAlreadyProcessed($webhookLog);
            }

            $transaction->status = $payload['status'] === 1 ? TransactionStatus::SUCCESS : TransactionStatus::FAILED;
            $transaction->save();

            $webhookLog->update([
                'status' => $transaction->status,
            ]);

            return response()->json([
                'message' => PaymentResponseMessages::WEBHOOK_PROCESSED,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al procesar webhook: '.$e->getMessage(), [
                'exception' => $e,
                'request_payload' => $request->getContent(),
            ]);

            return $this->handleInternalError($e);
        }
    }

    private function handleInvalidData($payload)
    {
        $message = ! isset($payload['transaction_id']) ? PaymentResponseMessages::INVALID_TRANSACTION_ID : PaymentResponseMessages::INVALID_STATUS;

        return response()->json([
            'message' => $message,
        ], 422);
    }

    private function handleTransactionNotFound($webhookLog)
    {
        $webhookLog->update([
            'status' => PaymentResponseMessages::TRANSACTION_NOT_FOUND,
        ]);

        return response()->json([
            'message' => PaymentResponseMessages::TRANSACTION_NOT_FOUND,
        ], 404);
    }

    private function handleTransactionAlreadyProcessed($webhookLog)
    {
        $webhookLog->update([
            'status' => PaymentResponseMessages::TRANSACTION_ALREADY_PROCESSED,
        ]);

        return response()->json([
            'message' => PaymentResponseMessages::TRANSACTION_ALREADY_PROCESSED,
        ], 200);
    }

    private function handleInternalError(\Throwable $e)
    {
        return response()->json([
            'message' => PaymentResponseMessages::INTERNAL_ERROR,
            'error' => 'Ocurrió un error interno.',
            'details' => $e->getMessage(),
            'line' => $e->getLine(),
        ], 500);
    }
}
