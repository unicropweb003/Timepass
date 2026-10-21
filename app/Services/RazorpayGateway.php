<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

class RazorpayGateway
{
    private const API_URL = 'https://api.razorpay.com/v1';

    public function configured(): bool
    {
        return $this->keyId() !== '' && $this->keySecret() !== '';
    }

    public function keyId(): string
    {
        return trim((string) config('services.razorpay.key_id', ''));
    }

    public function createOrder(int $amountPaise, string $receipt): array
    {
        $this->requireConfiguration();

        if ($amountPaise < 100 || $receipt === '' || strlen($receipt) > 40) {
            throw new InvalidArgumentException('A Razorpay order requires at least INR 1 and a receipt of 1–40 characters.');
        }

        $response = Http::withBasicAuth($this->keyId(), $this->keySecret())
            ->acceptJson()
            ->timeout(20)
            ->post(self::API_URL.'/orders', [
                'amount' => $amountPaise,
                'currency' => 'INR',
                'receipt' => $receipt,
            ])
            ->throw();

        $order = $response->json();

        if (! is_array($order)
            || ! is_string($order['id'] ?? null)
            || ! str_starts_with($order['id'], 'order_')
            || ($order['amount'] ?? null) !== $amountPaise
            || ($order['currency'] ?? null) !== 'INR') {
            throw new RuntimeException('Razorpay returned an invalid order response.');
        }

        return $order;
    }

    public function fetchPayment(string $paymentId): array
    {
        $this->requireConfiguration();

        if (! preg_match('/\Apay_[A-Za-z0-9]+\z/', $paymentId)) {
            throw new InvalidArgumentException('Invalid Razorpay payment ID.');
        }

        $response = Http::withBasicAuth($this->keyId(), $this->keySecret())
            ->acceptJson()
            ->timeout(20)
            ->get(self::API_URL.'/payments/'.$paymentId)
            ->throw();

        $payment = $response->json();

        if (! is_array($payment) || ($payment['id'] ?? null) !== $paymentId) {
            throw new RuntimeException('Razorpay returned an invalid payment response.');
        }

        return $payment;
    }

    public function validCheckoutSignature(string $storedOrderId, string $paymentId, string $signature): bool
    {
        $secret = $this->keySecret();

        if ($secret === '' || $storedOrderId === '' || $paymentId === '' || strlen($signature) !== 64) {
            return false;
        }

        $expected = hash_hmac('sha256', $storedOrderId.'|'.$paymentId, $secret);

        return hash_equals($expected, $signature);
    }

    public function validWebhookSignature(string $rawBody, string $signature): bool
    {
        $secret = trim((string) config('services.razorpay.webhook_secret', ''));

        if ($secret === '' || $rawBody === '' || strlen($signature) !== 64) {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, $signature);
    }

    private function keySecret(): string
    {
        return trim((string) config('services.razorpay.key_secret', ''));
    }

    private function requireConfiguration(): void
    {
        if (! $this->configured()) {
            throw new RuntimeException('Razorpay is not configured.');
        }
    }
}
