<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;
use UnexpectedValueException;

class StripePaymentController extends Controller
{
    private function stripe(): StripeClient
    {
        return new StripeClient(config('services.stripe.secret'));
    }

    public function checkout(Payment $payment)
    {
        if ($payment->user_id !== Auth::id()) {
            abort(403);
        }

        if ($payment->payment_method !== 'card') {
            return redirect()
                ->route('citizen.requests.show', $payment->request_id)
                ->withErrors([
                    'payment' => 'This payment is not a card payment.',
                ]);
        }

        if ($payment->status === 'success') {
            return redirect()
                ->route('citizen.requests.show', $payment->request_id)
                ->with('success', 'This payment is already completed.');
        }

        if (! config('services.stripe.secret')) {
            return redirect()
                ->route('citizen.requests.show', $payment->request_id)
                ->withErrors([
                    'payment' => 'Stripe secret key is not configured.',
                ]);
        }

        $payment->load(['request.service', 'user']);

        $amountInCents = (int) round((float) $payment->amount * 100);

        if ($amountInCents <= 0) {
            return redirect()
                ->route('citizen.requests.show', $payment->request_id)
                ->withErrors([
                    'payment' => 'Invalid payment amount.',
                ]);
        }

        $serviceName = $payment->request?->service?->name ?? 'Municipality Service';
        $requestNumber = $payment->request?->request_number ?? ('Request #' . $payment->request_id);
        $currency = strtolower($payment->currency ?: config('services.stripe.currency', 'usd'));

        $checkoutData = [
            'payment_method_types' => ['card'],
            'mode' => 'payment',

            'client_reference_id' => (string) $payment->id,

            'line_items' => [
                [
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => $currency,
                        'unit_amount' => $amountInCents,
                        'product_data' => [
                            'name' => $serviceName,
                            'description' => $requestNumber,
                        ],
                    ],
                ],
            ],

            'success_url' => route('citizen.payments.stripe.success', $payment)
                . '?session_id={CHECKOUT_SESSION_ID}',

            'cancel_url' => route('citizen.payments.stripe.cancel', $payment),

            'metadata' => [
                'payment_id' => (string) $payment->id,
                'request_id' => (string) $payment->request_id,
                'user_id' => (string) $payment->user_id,
            ],

            'payment_intent_data' => [
                'metadata' => [
                    'payment_id' => (string) $payment->id,
                    'request_id' => (string) $payment->request_id,
                    'user_id' => (string) $payment->user_id,
                ],
            ],
        ];

        if (! empty($payment->user?->email)) {
            $checkoutData['customer_email'] = $payment->user->email;
        }

        $session = $this->stripe()->checkout->sessions->create($checkoutData);

        $payment->update([
            'provider' => 'stripe',
            'transaction_reference' => $session->id,
        ]);

        return redirect()->away($session->url);
    }

    public function success(Request $request, Payment $payment)
    {
        if ($payment->user_id !== Auth::id()) {
            abort(403);
        }

        $sessionId = $request->query('session_id');

        if ($sessionId && $sessionId === $payment->transaction_reference) {
            try {
                $session = $this->stripe()->checkout->sessions->retrieve($sessionId, []);

                if ($session->payment_status === 'paid') {
                    $this->markPaymentAsSuccessful($payment, $session);
                }
            } catch (\Exception $exception) {
                Log::warning('Stripe success page sync failed.', [
                    'payment_id' => $payment->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return redirect()
            ->route('citizen.requests.show', $payment->request_id)
            ->with('success', 'Payment completed. Your request was submitted successfully.');
    }

    public function cancel(Payment $payment)
    {
        if ($payment->user_id !== Auth::id()) {
            abort(403);
        }

        return redirect()
            ->route('citizen.requests.show', $payment->request_id)
            ->withErrors([
                'payment' => 'Card payment was cancelled. Your request is saved, but the payment is still pending.',
            ]);
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        if (! $secret) {
            Log::error('Stripe webhook secret is not configured.');

            return response()->json([
                'error' => 'Webhook secret not configured',
            ], 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (UnexpectedValueException $exception) {
            return response()->json([
                'error' => 'Invalid payload',
            ], 400);
        } catch (SignatureVerificationException $exception) {
            return response()->json([
                'error' => 'Invalid signature',
            ], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $payment = Payment::where('transaction_reference', $session->id)->first();

            if (! $payment && ! empty($session->metadata->payment_id)) {
                $payment = Payment::find($session->metadata->payment_id);
            }

            if ($payment && $session->payment_status === 'paid') {
                $this->markPaymentAsSuccessful($payment, $session);
            }
        }

        return response()->json([
            'received' => true,
        ]);
    }

    private function markPaymentAsSuccessful(Payment $payment, $session): void
    {
        DB::transaction(function () use ($payment, $session) {
            $payment->refresh();

            $wasAlreadySuccessful = $payment->status === 'success';

            $payment->update([
                'provider' => 'stripe',
                'status' => 'success',
                'transaction_reference' => $session->id,
                'paid_at' => $payment->paid_at ?? now(),
            ]);

            $providerReference = (string) ($session->payment_intent ?? $session->id);

            PaymentTransaction::updateOrCreate(
                [
                    'payment_id' => $payment->id,
                    'provider_reference' => $providerReference,
                ],
                [
                    'transaction_type' => 'stripe_checkout',
                    'tx_hash' => null,
                    'status' => 'success',
                    'processed_at' => now(),
                ]
            );

            if (! $wasAlreadySuccessful) {
                Notification::create([
                    'user_id' => $payment->user_id,
                    'type' => 'payment_completed',
                    'title' => 'Payment completed',
                    'message' => 'Your card payment for request #' . $payment->request_id . ' was completed successfully.',
                    'channel' => 'system',
                    'is_read' => false,
                ]);
            }
        });
    }
}