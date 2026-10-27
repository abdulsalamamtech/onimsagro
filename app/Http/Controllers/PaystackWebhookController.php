<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\PaymentConfirmationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaystackWebhookController extends Controller
{
    /**
     * [public] Paystack calls this when a payment event happens, so payments are
     * confirmed even if the customer never returns to the website.
     *
     * Set in Paystack Dashboard → Settings → API Keys & Webhooks.
     */
    public function __invoke(Request $request, PaymentConfirmationService $payments)
    {
        if (! $this->hasValidSignature($request)) {
            Log::warning('Paystack webhook rejected: invalid signature', ['ip' => $request->ip()]);

            return ApiResponse::error([], 'invalid signature', 401);
        }

        // From here on always answer 200, otherwise Paystack keeps retrying.
        try {
            $event = $request->input('event');
            $data = $request->input('data');
            $reference = is_array($data) ? ($data['reference'] ?? null) : null;

            if ($event !== 'charge.success') {
                return ApiResponse::success([], 'event ignored');
            }

            if (! is_string($reference) || $reference === '') {
                Log::warning('Paystack webhook charge.success without a reference');

                return ApiResponse::success([], 'event ignored');
            }

            $result = $payments->confirm($reference, $data);
            info('Paystack webhook processed', ['reference' => $reference, 'result' => $result->status]);
        } catch (\Throwable $th) {
            Log::error('Paystack webhook processing failed', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
        }

        return ApiResponse::success([], 'webhook received');
    }

    /**
     * Paystack signs the raw request body with HMAC SHA512 using our secret key.
     */
    private function hasValidSignature(Request $request): bool
    {
        $secret = config('services.paystack.secret');
        $signature = $request->header('x-paystack-signature');

        if (! is_string($secret) || $secret === '' || ! is_string($signature) || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha512', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }
}
