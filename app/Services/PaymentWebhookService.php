<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Carrier;
use App\Services\CarrierSelectorService;
use App\Services\ImeiReservationService;
use App\Services\ShippingService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PaymentWebhookService
{
    public function __construct(private ImeiReservationService $imeiService, private ShippingService $shippingService, private CarrierSelectorService $carrierSelector) {}

    /**
     * Verify signature for known gateways. Returns true if valid or no secret configured.
     */
    protected function verifySignature(string $gateway, Request $request): bool
    {
        $signature = $request->input('signature') ?? $request->header('X-Signature');

        if (! $signature) {
            // No signature provided; allow in local/dev but log.
            Log::warning('Webhook without signature for gateway: ' . $gateway);
            return app()->environment('production') ? false : true;
        }

        $secretKey = match (strtolower($gateway)) {
            'momo' => env('MOMO_SECRET'),
            'vnpay' => env('VNPAY_SECRET'),
            'zalopay' => env('ZALOPAY_SECRET'),
            default => null,
        };

        if (! $secretKey) {
            // no configured secret — allow but log
            Log::info("No secret configured for gateway {$gateway}; skipping signature verification.");
            return true;
        }

        $payload = $request->getContent();
        $computed = hash_hmac('sha256', $payload, $secretKey);

        return hash_equals($computed, $signature);
    }

    /**
     * Process incoming webhook. Returns array [statusCode, message]
     */
    public function handle(Request $request): array
    {
        $gateway = $request->input('gateway') ?? $request->header('X-Gateway') ?? 'unknown';

        if (! $this->verifySignature($gateway, $request)) {
            Log::warning('Webhook signature verification failed', ['gateway' => $gateway]);
            return [403, 'Invalid signature'];
        }

        $transaction = $request->input('transaction_code');
        $paymentId = $request->input('payment_id');
        $status = strtolower($request->input('status', 'failed'));

        $payment = null;
        if ($transaction) {
            $payment = Payment::where('transaction_code', $transaction)->first();
        }

        if (! $payment && $paymentId) {
            $payment = Payment::find($paymentId);
        }

        if (! $payment) {
            Log::warning('Webhook payment not found', ['transaction' => $transaction, 'payment_id' => $paymentId]);
            return [404, 'Payment not found'];
        }

        // Idempotency: if already processed, return OK
        if ($payment->payment_status === 'paid' && $status === 'success') {
            return [200, 'Already processed'];
        }

        if ($status === 'success') {
            $payment->payment_status = 'paid';
            $payment->paid_at = Carbon::now();
            $payment->transaction_code = $transaction ?? $payment->transaction_code;
            $payment->save();

            $this->imeiService->finalize($payment->order);

            // mark order as processing
            $order = $payment->order;
            $order->status = 'processing';
            $order->save();

            // create shipment using default carrier if available
            try {
                $carrier = $this->carrierSelector->selectForOrder($order);
                if ($carrier) {
                    $this->shippingService->createShipment($order, $carrier, ['source' => 'auto']);
                } else {
                    Log::info('No active carrier found; skipping shipment creation for order ' . $order->id);
                }
            } catch (\Exception $e) {
                if (app()->environment('testing')) {
                    throw $e;
                }

                Log::error('Error creating shipment for order ' . $order->id . ': ' . $e->getMessage());
            }

            return [200, 'OK'];
        }

        // failure
        $payment->payment_status = 'failed';
        $payment->transaction_code = $transaction ?? $payment->transaction_code;
        $payment->save();

        $this->imeiService->release($payment->order);

        return [200, 'Failed'];
    }
}
