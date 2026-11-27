<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class IyzicoSandboxService
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *     status: string,
     *     mode: string,
     *     token: string,
     *     three_ds_html: ?string,
     *     error_message: ?string
     * }
     */
    public function initializeThreeDs(array $payload): array
    {
        $simulation = (bool) config('iyzico.simulation', true);
        $apiKey = (string) config('iyzico.api_key', '');
        $secretKey = (string) config('iyzico.secret_key', '');

        if ($simulation || $apiKey === '' || $secretKey === '') {
            $cardNumber = preg_replace('/\D+/', '', (string) ($payload['card_number'] ?? '')) ?? '';

            // Test kartı 4546711234567894 veya sonu 0000 ise başlatma aşamasında simüle reddetme
            if (str_ends_with($cardNumber, '0000')) {
                return [
                    'status' => 'failure',
                    'mode' => 'simulation',
                    'token' => '',
                    'three_ds_html' => null,
                    'error_message' => 'Kart bakiyesi yetersiz veya banka tarafından reddedildi (Test Kartı).',
                ];
            }

            return [
                'status' => 'success',
                'mode' => 'simulation',
                'token' => '3DS-'.strtoupper(Str::random(20)),
                'three_ds_html' => null,
                'error_message' => null,
            ];
        }

        $baseUrl = rtrim((string) config('iyzico.base_url', 'https://sandbox-api.iyzipay.com'), '/');
        $uriPath = '/payment/3dsecure/initialize';
        $randomKey = (string) Str::uuid();

        $body = [
            'locale' => 'tr',
            'conversationId' => $payload['idempotency_key'],
            'price' => number_format((float) $payload['subtotal'], 2, '.', ''),
            'paidPrice' => number_format((float) $payload['total'], 2, '.', ''),
            'currency' => 'TRY',
            'installment' => 1,
            'basketId' => 'BASKET-'.$payload['user_id'],
            'paymentChannel' => 'WEB',
            'paymentGroup' => 'PRODUCT',
            'callbackUrl' => $payload['callback_url'],
            'paymentCard' => [
                'cardHolderName' => $payload['card_holder'],
                'cardNumber' => preg_replace('/\D+/', '', (string) $payload['card_number']),
                'expireMonth' => $payload['expire_month'],
                'expireYear' => $payload['expire_year'],
                'cvc' => $payload['cvc'],
                'registerCard' => 0,
            ],
            'buyer' => $payload['buyer'],
            'shippingAddress' => $payload['shipping_address'],
            'billingAddress' => $payload['shipping_address'],
            'basketItems' => $payload['basket_items'],
        ];

        $jsonBody = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
        $authHeader = $this->generateAuthorizationHeader($apiKey, $secretKey, $randomKey, $uriPath, $jsonBody);

        $response = Http::withHeaders([
            'Authorization' => $authHeader,
            'x-iyzi-rnd' => $randomKey,
            'Content-Type' => 'application/json',
        ])->withBody($jsonBody, 'application/json')->post($baseUrl.$uriPath);

        $data = $response->json() ?? [];

        if (($data['status'] ?? 'failure') !== 'success') {
            return [
                'status' => 'failure',
                'mode' => 'iyzico_sandbox',
                'token' => '',
                'three_ds_html' => null,
                'error_message' => (string) ($data['errorMessage'] ?? 'iyzico 3D Secure başlatılamadı.'),
            ];
        }

        return [
            'status' => 'success',
            'mode' => 'iyzico_sandbox',
            'token' => (string) ($data['paymentId'] ?? Str::random(16)),
            'three_ds_html' => isset($data['threeDSHtmlContent']) ? base64_decode((string) $data['threeDSHtmlContent']) : null,
            'error_message' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $callbackData
     * @return array{
     *     status: string,
     *     transaction_id: string,
     *     raw_response: array<string, mixed>,
     *     error_message: ?string
     * }
     */
    public function verifyThreeDsCallback(array $callbackData): array
    {
        $status = (string) ($callbackData['status'] ?? 'failure');
        $mdStatus = (string) ($callbackData['mdStatus'] ?? '0');
        $paymentId = (string) ($callbackData['paymentId'] ?? 'IYZ-'.strtoupper(Str::random(12)));

        if ($status !== 'success' || $mdStatus !== '1') {
            return [
                'status' => 'failed',
                'transaction_id' => $paymentId,
                'raw_response' => $callbackData,
                'error_message' => (string) ($callbackData['errorMessage'] ?? '3D Secure doğrulaması başarısız oldu veya iptal edildi.'),
            ];
        }

        return [
            'status' => 'success',
            'transaction_id' => $paymentId,
            'raw_response' => $callbackData,
            'error_message' => null,
        ];
    }

    private function generateAuthorizationHeader(
        string $apiKey,
        string $secretKey,
        string $randomKey,
        string $uriPath,
        string $jsonBody
    ): string {
        $signature = hash_hmac('sha256', $randomKey.$uriPath.$jsonBody, $secretKey);
        $authString = sprintf('apiKey:%s&randomKey:%s&signature:%s', $apiKey, $randomKey, $signature);

        return 'IYZWSv2 '.base64_encode($authString);
    }
}
