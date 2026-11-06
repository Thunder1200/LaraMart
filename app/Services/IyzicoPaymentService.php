<?php

namespace App\Services;

use Illuminate\Support\Str;

class IyzicoPaymentService
{
    public function chargeSandbox(array $payload): array
    {
        $simulateFailure = (bool) ($payload['simulate_failure'] ?? false);
        $cardNumber = preg_replace('/\D/', '', (string) ($payload['card_number'] ?? '5528790000000008'));

        if ($simulateFailure || str_ends_with($cardNumber, '0000'))
        {
            return [
                'success' => false,
                'transaction_id' => null,
                'error_message' => 'Ödeme işlemi banka/iyzico sandbox tarafından reddedildi (Yetersiz bakiye veya 3D doğrulama hatası).',
                'raw_response' => [
                    'status' => 'failure',
                    'errorCode' => '10051',
                    'errorMessage' => 'Kart limiti yetersiz',
                    'locale' => 'tr',
                    'systemTime' => now()->timestamp,
                ],
            ];
        }

        $transactionId = 'IYZ-SBX-' . strtoupper(Str::random(10));

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'error_message' => null,
            'raw_response' => [
                'status' => 'success',
                'paymentId' => $transactionId,
                'fraudStatus' => 1,
                'paidPrice' => $payload['amount'] ?? '0.00',
                'currency' => 'TRY',
                'basketId' => $payload['basket_id'] ?? 'BASKET-1',
                'mdStatus' => 1,
                'threeDSecure' => true,
                'systemTime' => now()->timestamp,
            ],
        ];
    }
}

