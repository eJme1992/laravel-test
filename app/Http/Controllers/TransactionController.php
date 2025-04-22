<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use OpenApi\Attributes as OA;

#[OA\Server(url: 'http://localhost')] // Added #[OA\Server]
#[OA\Tag(name: 'Transactions', description: 'Operations related to transactions')]
class TransactionController extends Controller
{
    /**
     * Display a listing of the transactions.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    #[OA\Get(
        path: '/transactions', // Corrected path
        operationId: 'index',
        summary: 'Get a list of transactions',
        tags: ['Transactions'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'payment_provider_id', type: 'integer', example: 1),
                                new OA\Property(property: 'amount', type: 'number', format: 'float', example: 100.00),
                                new OA\Property(property: 'currency', type: 'string', example: 'USD'),
                                new OA\Property(property: 'external_transaction_id', type: 'string', nullable: true, example: 'some-ext-id'),
                                new OA\Property(property: 'status', type: 'string', example: 'pending'),
                                new OA\Property(property: 'error_message', type: 'string', nullable: true, example: null),
                                new OA\Property(
                                    property: 'provider',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'name', type: 'string', example: 'easymoney'),
                                        new OA\Property(property: 'base_url', type: 'string', example: 'http://localhost:3001'),
                                        new OA\Property(property: 'api_key', type: 'string', nullable: true, example: null),
                                        new OA\Property(property: 'callback_url', type: 'string', nullable: true, example: null),
                                        new OA\Property(property: 'supports_webhook', type: 'boolean', example: false),
                                        new OA\Property(property: 'active', type: 'boolean', example: true),
                                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                    ],
                                    type: 'object'
                                ),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                            ],
                            type: 'object'
                        )
                    )
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Error al obtener las transacciones',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'message', type: 'string', example: 'Error al obtener las transacciones'),
                            new OA\Property(property: 'error', type: 'string', example: 'Error message'),
                        ]
                    )
                )
            ),
        ]
    )]
    public function index()
    {
        try {
            $transactions = Transaction::with('provider')->latest()->get();

            return response()->json($transactions);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al obtener las transacciones',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified transaction.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    #[OA\Get(
        path: '/transactions/{id}', // Corrected path
        operationId: 'show',
        summary: 'Get a single transaction',
        tags: ['Transactions'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID of transaction',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'payment_provider_id', type: 'integer', example: 1),
                            new OA\Property(property: 'amount', type: 'number', format: 'float', example: 100.00),
                            new OA\Property(property: 'currency', type: 'string', example: 'USD'),
                            new OA\Property(property: 'external_transaction_id', type: 'string', nullable: true, example: 'some-ext-id'),
                            new OA\Property(property: 'status', type: 'string', example: 'pending'),
                            new OA\Property(property: 'error_message', type: 'string', nullable: true, example: null),
                            new OA\Property(
                                property: 'provider',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'easymoney'),
                                    new OA\Property(property: 'base_url', type: 'string', example: 'http://localhost:3001'),
                                    new OA\Property(property: 'api_key', type: 'string', nullable: true, example: null),
                                    new OA\Property(property: 'callback_url', type: 'string', nullable: true, example: null),
                                    new OA\Property(property: 'supports_webhook', type: 'boolean', example: false),
                                    new OA\Property(property: 'active', type: 'boolean', example: true),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                ],
                                type: 'object'
                            ),
                            new OA\Property(
                                property: 'request_logs',
                                type: 'array',
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'transaction_id', type: 'integer', example: 1),
                                        new OA\Property(property: 'payload', type: 'object', example: []),
                                        new OA\Property(property: 'response', type: 'object', example: []),
                                        new OA\Property(property: 'status_code', type: 'integer', example: 200),
                                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                    ],
                                    type: 'object'
                                )
                            ),
                            new OA\Property(
                                property: 'webhook_logs',
                                type: 'array',
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'payload', type: 'object', example: []),
                                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                    ],
                                    type: 'object'
                                )
                            ),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
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
                            new OA\Property(property: 'message', type: 'string', example: 'Transacción no encontrada'),
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Error al obtener la transacción',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'message', type: 'string', example: 'Error al obtener la transacción'),
                            new OA\Property(property: 'error', type: 'string', example: 'Error message'),
                        ]
                    )
                )
            ),
        ]
    )]
    public function show($id)
    {
        try {
            $transaction = Transaction::with(['provider', 'requestLogs', 'webhookLogs'])->findOrFail($id);

            return response()->json($transaction);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Transacción no encontrada',
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al obtener la transacción',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
