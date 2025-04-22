<?php

namespace App\Http\Controllers;

use App\Constants\PaymentResponseMessages;
use App\Models\PaymentProvider; // Import PaymentProvider
use App\Services\PaymentService; // Import PaymentService
use Illuminate\Http\Request; // Import OpenApi
use OpenApi\Attributes as OA; // Import PaymentResponseMessages

#[OA\Info(version: '1.0.0', title: 'Payment API')]
#[OA\Server(url: 'http://127.0.0.1:8000/')]
#[OA\Tag(name: 'Payments', description: 'Operations related to payment processing')]
class PaymentController extends Controller
{
    // ... your existing code ...

    /**
     * Process a payment for a given provider.
     *
     * @param string $providerSlug
     * @return \Illuminate\Http\JsonResponse
     */
    #[OA\Post(
        path: '/payments/{providerSlug}',
        operationId: 'processPayment',
        summary: 'Process a payment',
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        required: ['amount', 'currency'],
                        properties: [
                            new OA\Property(property: 'amount', type: 'number', format: 'float', example: 100.00),
                            new OA\Property(property: 'currency', type: 'string', example: 'USD'),
                        ],
                    )
                ),
            ]
        ),
        tags: ['Payments'],
        responses: [
            new OA\Response(
                response: 200,
                description: PaymentResponseMessages::SWAGGER_SUCCESS_DESCRIPTION,
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
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 500,
                description: PaymentResponseMessages::SWAGGER_FAILED_DESCRIPTION,
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'error', type: 'string', example: PaymentResponseMessages::SWAGGER_FAILED_DESCRIPTION),
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 422,
                description: PaymentResponseMessages::SWAGGER_VALIDATION_DESCRIPTION,
                content: new OA\MediaType(
                    mediaType: 'application/json',
                )
            ),
            new OA\Response(
                response: 404,
                description: PaymentResponseMessages::SWAGGER_NOT_FOUND_DESCRIPTION,
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'message', type: 'string', example: PaymentResponseMessages::PROVIDER_NOT_FOUND),
                        ]
                    )
                )
            ),
        ]
    )]
    public function processPayment(Request $request, $providerSlug)
    {
    try {

        $provider = PaymentProvider::where('name', $providerSlug)->where('active', true)->firstOrFail();

        $data = $request->validate([
            'amount' => 'required|numeric',
            'currency' => 'required|string',
        ]);

        $paymentService = new PaymentService();

        $result = $paymentService->process($provider, $data);

        if ($result->status === 'failed') {
            // Log the error message
            \Log::error(PaymentResponseMessages::PAYMENT_FAILED.$result->error_message);

            return response()->json([
                'error' => $result->error_message,
            ], 422);
        }

        return response()->json($result);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        \Log::error(PaymentResponseMessages::PROVIDER_NOT_FOUND.$e->getMessage());

        return response()->json([
            'message' => PaymentResponseMessages::PROVIDER_NOT_FOUND,
        ], 404);
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error(PaymentResponseMessages::VALIDATION_ERROR.$e->getMessage());

        return response()->json([
            'error' => PaymentResponseMessages::VALIDATION_ERROR,
            'details' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        \Log::error(PaymentResponseMessages::PAYMENT_PROCESSING_ERROR.$e->getMessage());

        return response()->json([
            'error' => PaymentResponseMessages::PAYMENT_FAILED.$e->getMessage(),
        ], 500);
    }
}
}
