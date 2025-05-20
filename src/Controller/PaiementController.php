<?php

namespace App\Controller;

use App\Entity\Paiement;
use App\Entity\Sortie;
use App\Repository\PaiementRepository;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


#[Route('/paiement')]
class PaiementController extends AbstractController
{
    public function __construct(
        private StripeService $stripeService,
        private EntityManagerInterface $entityManager,
        private PaiementRepository $paiementRepository
    ) {}

    #[IsGranted('ROLE_USER')]
    #[Route('/checkout/{id}', name: 'create_checkout_session', methods: ['POST'])]
    public function createCheckoutSession(Sortie $sortie, Security $security, Request $request): Response
    {
        $user = $security->getUser();

        // Log de debug immédiat
        error_log("[PAYMENT_DEBUG] Checkout session creation started for sortie {$sortie->getId()}, user {$user->getId()}");

        // Vérifications préalables avec messages d'erreur détaillés
        if ($sortie->getEtat()->getLibelle() !== 'Ouverte') {
            $error = 'Cette sortie n\'est plus ouverte aux paiements.';
            error_log("[PAYMENT_DEBUG] Error: $error");

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => $error], 400);
            }
            $this->addFlash('danger', $error);
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        // Vérifier si l'utilisateur a déjà payé
        $existingPayment = $this->paiementRepository->findPaidPaymentForUserAndSortie(
            $user->getId(),
            $sortie->getId()
        );

        if ($existingPayment) {
            $error = 'Vous avez déjà payé pour cette sortie.';
            error_log("[PAYMENT_DEBUG] Error: $error (existing payment #{$existingPayment->getId()})");

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => $error], 400);
            }
            $this->addFlash('warning', $error);
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        // Autres vérifications...
        if ($sortie->getNombreInscritsTotal() >= $sortie->getNbInscriptionMax()) {
            $error = 'Cette sortie est complète.';
            error_log("[PAYMENT_DEBUG] Error: $error");

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => $error], 400);
            }
            $this->addFlash('danger', $error);
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        if ($sortie->getDateLimiteInscription() < new \DateTime()) {
            $error = 'La date limite d\'inscription est dépassée.';
            error_log("[PAYMENT_DEBUG] Error: $error");

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => $error], 400);
            }
            $this->addFlash('danger', $error);
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        // ÉTAPE CRITIQUE: Créer l'entité Paiement AVANT la session Stripe
        try {
            $paiement = new Paiement();
            $paiement->setMontant($sortie->getPrix() * 100); // Convertir en centimes
            $paiement->setUser($user);
            $paiement->setSortie($sortie);
            $paiement->setStatus('pending');
            $paiement->setCreatedAt(new \DateTimeImmutable());

            // IMPORTANT: Persister et flusher IMMÉDIATEMENT
            $this->entityManager->persist($paiement);
            $this->entityManager->flush();

            error_log("[PAYMENT_DEBUG] Payment entity created with ID: {$paiement->getId()}");

        } catch (\Exception $e) {
            $error = 'Erreur lors de la création du paiement: ' . $e->getMessage();
            error_log("[PAYMENT_DEBUG] Payment entity creation failed: {$e->getMessage()}");

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => $error], 500);
            }
            $this->addFlash('danger', $error);
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        // Générer les URLs avec l'ID du paiement - URLS ABSOLUES
        try {
            $successUrl = $this->generateUrl('paiement_success', [
                    'paiement_id' => $paiement->getId()
                ], UrlGeneratorInterface::ABSOLUTE_URL) . '&session_id={CHECKOUT_SESSION_ID}';

            $cancelUrl = $this->generateUrl('paiement_cancel', [
                'paiement_id' => $paiement->getId()
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            error_log("[PAYMENT_DEBUG] URLs generated - Success: $successUrl, Cancel: $cancelUrl");

        } catch (\Exception $e) {
            $error = 'Erreur lors de la génération des URLs: ' . $e->getMessage();
            error_log("[PAYMENT_DEBUG] URL generation failed: {$e->getMessage()}");

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => $error], 500);
            }
            $this->addFlash('danger', $error);
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        // Créer la session Stripe avec validation rigoureuse
        $metadata = [
            'paiement_id' => (string)$paiement->getId(),
            'sortie_id' => (string)$sortie->getId(),
            'user_id' => (string)$user->getId(),
            'sortie_titre' => substr($sortie->getTitre(), 0, 100), // Limiter à 100 caractères
            'user_email' => $user->getEmail(),
            'environment' => $this->getParameter('kernel.environment'),
        ];

        error_log("[PAYMENT_DEBUG] Creating Stripe session with metadata: " . json_encode($metadata));

        $result = $this->stripeService->createCheckoutSession(
            $paiement->getMontant(),
            'eur',
            $successUrl,
            $cancelUrl,
            $metadata
        );

        if (!$result['success']) {
            // En cas d'erreur Stripe, supprimer le paiement créé
            try {
                $this->entityManager->remove($paiement);
                $this->entityManager->flush();
                error_log("[PAYMENT_DEBUG] Payment entity removed after Stripe error");
            } catch (\Exception $cleanupError) {
                error_log("[PAYMENT_DEBUG] Failed to cleanup payment: {$cleanupError->getMessage()}");
            }

            $error = 'Erreur Stripe: ' . $result['error'];
            if (isset($result['stripe_code'])) {
                $error .= " (Code: {$result['stripe_code']})";
            }
            error_log("[PAYMENT_DEBUG] Stripe session creation failed: $error");

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                    'error' => $error,
                    'details' => $result
                ], 500);
            }
            $this->addFlash('danger', $error);
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        // Mettre à jour le paiement avec l'ID de session Stripe
        try {
            $paiement->setStripeCheckoutSessionId($result['session_id']);
            $this->entityManager->flush();

            error_log("[PAYMENT_DEBUG] Payment updated with Stripe session ID: {$result['session_id']}");

        } catch (\Exception $e) {
            error_log("[PAYMENT_DEBUG] Failed to update payment with session ID: {$e->getMessage()}");
            // Continuer quand même - on peut récupérer via les métadonnées
        }

        // Log final de succès
        error_log("[PAYMENT_DEBUG] Stripe session created successfully. Redirecting to: {$result['url']}");

        // Pour les requêtes AJAX, retourner l'URL au lieu de rediriger
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'redirect_url' => $result['url'],
                'session_id' => $result['session_id'],
                'paiement_id' => $paiement->getId()
            ]);
        }else{

        // Redirection classique
        return new RedirectResponse($result['url']);
    }}

    #[Route('/success', name: 'paiement_success', methods: ['GET'])]
    public function paymentSuccess(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');
        $paiementId = $request->query->get('paiement_id');

        error_log("[PAYMENT_DEBUG] Payment success page accessed - Session: $sessionId, Payment: $paiementId");

        // Recherche multiple du paiement
        $paiement = null;

        // 1. Par ID de paiement
        if ($paiementId) {
            $paiement = $this->paiementRepository->find($paiementId);
            if ($paiement) {
                error_log("[PAYMENT_DEBUG] Payment found by ID");
            }
        }

        // 2. Par session Stripe
        if (!$paiement && $sessionId) {
            $paiement = $this->paiementRepository->findByStripeCheckoutSessionId($sessionId);
            if ($paiement) {
                error_log("[PAYMENT_DEBUG] Payment found by Stripe session");
            }
        }

        // 3. Par métadonnées Stripe
        if (!$paiement && $sessionId) {
            $stripeResult = $this->stripeService->retrieveCheckoutSession($sessionId);
            if ($stripeResult['success'] && isset($stripeResult['session']->metadata['paiement_id'])) {
                $paiement = $this->paiementRepository->find($stripeResult['session']->metadata['paiement_id']);
                if ($paiement) {
                    error_log("[PAYMENT_DEBUG] Payment found by Stripe metadata");
                }
            }
        }

        if (!$paiement) {
            error_log("[PAYMENT_DEBUG] Payment not found by any method");
            $this->addFlash('danger', 'Paiement introuvable. Veuillez vérifier votre historique.');
            return $this->redirectToRoute('app_sortie_index');
        }

        // Synchroniser avec Stripe
        if ($sessionId) {
            $this->syncPaymentWithStripe($paiement, $sessionId);
        }

        return $this->render('paiement/success.html.twig', [
            'paiement' => $paiement,
            'sortie' => $paiement->getSortie(),
        ]);
    }

    #[Route('/cancel', name: 'paiement_cancel', methods: ['GET'])]
    public function paymentCancel(Request $request): Response
    {
        $paiementId = $request->query->get('paiement_id');


        // Marquer le paiement comme annulé si nécessaire
        if ($paiementId) {
            $paiement = $this->paiementRepository->find($paiementId);
            if ($paiement && $paiement->getStatus() === 'pending') {
                $paiement->setStatus('cancelled');
                $this->entityManager->flush();
            }
        }

        // Pour les requêtes normales, renvoyer soit la vue, soit une redirection
        // Option 1: Rediriger vers la page de la sortie (si on a l'ID de la sortie)
        $sortieId = null;
        if ($paiementId && $paiement) {
            $sortieId = $paiement->getSortie()->getId();
        }

        if ($sortieId) {
            return $this->redirectToRoute('app_sortie_show', [
                'id' => $sortieId,
                'cancelled' => 1  // Paramètre pour indiquer que le paiement a été annulé
            ]);
        }

        // Si pas d'ID de sortie, afficher la page d'annulation standard
        $this->addFlash('info', 'Paiement annulé. Vous pouvez réessayer quand vous le souhaitez.');
        return $this->render('paiement/cancel.html.twig');
    }

    private function syncPaymentWithStripe(Paiement $paiement, string $sessionId): void
    {
        $result = $this->stripeService->retrieveCheckoutSession($sessionId);

        if ($result['success']) {
            $session = $result['session'];

            if ($session->payment_status === 'paid' && $paiement->getStatus() !== 'paid') {
                $paiement->setStatus('paid');
                $paiement->setStripePaymentIntentId($session->payment_intent);
                $paiement->setPaidAt(new \DateTimeImmutable());

                // Inscrire automatiquement l'utilisateur
                $sortie = $paiement->getSortie();
                $user = $paiement->getUser();

                if (!$sortie->getParticipants()->contains($user)) {
                    $sortie->addParticipant($user);
                }

                $this->entityManager->flush();

                error_log("[PAYMENT_DEBUG] Payment confirmed and user registered");
                $this->addFlash('success', 'Paiement effectué avec succès ! Vous êtes maintenant inscrit à la sortie.');
            }
        }
    }

    private function handleFamilyPaymentConfirmation(Paiement $paiement): void
    {
        $metadata = $paiement->getMetadataArray();
        $sortie = $paiement->getSortie();

        if (!isset($metadata['membres']) || !is_array($metadata['membres'])) {
            error_log("[PAYMENT_DEBUG] No family members data in payment metadata");
            return;
        }

        $membresInscrits = 0;
        foreach ($metadata['membres'] as $membreData) {
            if (!isset($membreData['id'])) {
                continue;
            }

            $membre = $this->entityManager->getRepository('App\Entity\MembreFamille')->find($membreData['id']);
            if ($membre && !$sortie->getMembresFamilleInscrits()->contains($membre)) {
                $sortie->addMembresFamilleInscrit($membre);
                $membre->addSortie($sortie);
                $membresInscrits++;

                error_log("[PAYMENT_DEBUG] Family member {$membre->getPrenom()} {$membre->getNom()} registered");
            }
        }

        if ($membresInscrits > 0) {
            error_log("[PAYMENT_DEBUG] {$membresInscrits} family members registered successfully");
            $this->addFlash('success', "{$membresInscrits} membre(s) de famille inscrit(s) avec succès !");
        }
    }



    // Mover la méthode handleCheckoutSessionCompleted au niveau de la classe
    private function handleCheckoutSessionCompleted($session): void
    {
        $paiement = $this->paiementRepository->findByStripeCheckoutSessionId($session['id']);

        if ($paiement && $session['payment_status'] === 'paid') {
            $paiement->setStatus('paid');
            $paiement->setStripePaymentIntentId($session['payment_intent']);
            $paiement->setPaidAt(new \DateTimeImmutable());

            // Vérifier si c'est un paiement famille
            $metadata = $paiement->getMetadataArray();
            if (isset($metadata['type']) && $metadata['type'] === 'famille') {
                error_log("[PAYMENT_DEBUG] Processing family payment confirmation via webhook");
                $this->handleFamilyPaymentConfirmation($paiement);
            } else {
                // Paiement individuel classique
                $sortie = $paiement->getSortie();
                $user = $paiement->getUser();

                if (!$sortie->getParticipants()->contains($user)) {
                    $sortie->addParticipant($user);
                }
            }

            $this->entityManager->flush();

            error_log("[PAYMENT_DEBUG] Payment completed via webhook", [
                'paiement_id' => $paiement->getId(),
                'session_id' => $session['id']
            ]);
        }
    }

    private function handlePaymentIntentSucceeded($paymentIntent): void
    {
        $paiement = $this->paiementRepository->findByStripePaymentIntentId($paymentIntent['id']);

        if ($paiement && $paiement->getStatus() !== 'paid') {
            $paiement->setStatus('paid');
            $paiement->setPaidAt(new \DateTimeImmutable());

            // Vérifier si c'est un paiement famille
            $metadata = $paiement->getMetadataArray();
            if (isset($metadata['type']) && $metadata['type'] === 'famille') {
                error_log("[PAYMENT_DEBUG] Processing family payment confirmation via payment intent");
                $this->handleFamilyPaymentConfirmation($paiement);
            } else {
                // Paiement individuel classique
                $sortie = $paiement->getSortie();
                $user = $paiement->getUser();

                if (!$sortie->getParticipants()->contains($user)) {
                    $sortie->addParticipant($user);
                }
            }

            $this->entityManager->flush();

            error_log("[PAYMENT_DEBUG] Payment intent succeeded via webhook", [
                'paiement_id' => $paiement->getId(),
                'payment_intent_id' => $paymentIntent['id']
            ]);
        }
    }

    private function handlePaymentIntentFailed($paymentIntent): void
    {
        $paiement = $this->paiementRepository->findByStripePaymentIntentId($paymentIntent['id']);

        if ($paiement) {
            $paiement->setStatus('payment_failed');
            $this->entityManager->flush();

            error_log("[PAYMENT_DEBUG] Payment intent failed via webhook", [
                'paiement_id' => $paiement->getId(),
                'payment_intent_id' => $paymentIntent['id']
            ]);
        }
    }
}