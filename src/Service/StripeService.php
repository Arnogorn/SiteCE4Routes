<?php

namespace App\Service;

use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class StripeService
{
    private StripeClient $stripe;

    public function __construct(string $stripeSecretKey)
    {
        $this->stripe = new StripeClient($stripeSecretKey);
    }

    /**
     * Créer une session Checkout Stripe
     */
    public function createCheckoutSession(
        int $amount,
        string $currency = 'eur',
        string $successUrl = '',
        string $cancelUrl = '',
        array $metadata = []
    ): array {
        try {
            $session = $this->stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => $metadata['sortie_titre'] ?? 'Sortie équestre',
                            'description' => 'Inscription à la sortie équestre - Ecuries des 4 Routes',
                        ],
                        'unit_amount' => $amount,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => $metadata,
                'billing_address_collection' => 'auto',
                'locale' => 'fr',
                'customer_email' => $metadata['user_email'] ?? null,
                'expires_at' => time() + (30 * 60), // Session expire après 30 minutes
            ]);

            return [
                'success' => true,
                'session' => $session,
                'url' => $session->url,
                'session_id' => $session->id,
            ];

        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'stripe_code' => $e->getStripeCode(),
                'type' => $e->getError()->type ?? 'unknown',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erreur technique inattendue: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Récupérer une session Checkout
     */
    public function retrieveCheckoutSession(string $sessionId): array
    {
        try {
            $session = $this->stripe->checkout->sessions->retrieve($sessionId, [
                'expand' => ['payment_intent', 'line_items']
            ]);

            return [
                'success' => true,
                'session' => $session,
            ];

        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'stripe_code' => $e->getStripeCode(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erreur technique lors de la récupération: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Récupérer un PaymentIntent
     */
    public function retrievePaymentIntent(string $paymentIntentId): array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);

            return [
                'success' => true,
                'payment_intent' => $paymentIntent,
            ];

        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'stripe_code' => $e->getStripeCode(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erreur technique lors de la récupération: ' . $e->getMessage(),
            ];
        }
    }
}

