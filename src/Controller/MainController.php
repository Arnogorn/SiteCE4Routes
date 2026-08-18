<?php

namespace App\Controller;

use App\Repository\ContactRepository;
use App\Service\CitationQuotidienneService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route(path: '/', name: 'index', methods: ['GET'])]
    function index(ContactRepository $contactRepository, CitationQuotidienneService $citationService): Response
    {
        $contact = $contactRepository->findAll();
        $todayQuote = $citationService->getTodayQuote();
        return $this->render('main/index.html.twig', [
            'contacts' => $contact,
            'dailyQuote' => $todayQuote,
        ]);
    }

    #[Route(path: '/politique-de-confidentialite', name: 'politique_confidentialite', methods: ['GET'])]
    function politiqueConfidentialite(): Response
    {
        return $this->render('main/politique_confidentialite.html.twig');
    }


    #[Route('/send-contact', name: 'send_contact', methods: ['POST'])]
    public function sendContact(Request $request, MailerInterface $mailer): Response
    {
        // 1. PROTECTION TEMPORELLE - Vérifier le timestamp
        $formTimestamp = (int)$request->request->get('form_timestamp');
        $currentTime = time() * 1000; // Convertir en millisecondes

        // Vérifier que le timestamp existe et est valide
        if (empty($formTimestamp) || $formTimestamp <= 0) {
            $this->addFlash('error', 'Erreur de sécurité détectée.');
            return $this->redirectToRoute('index');
        }

        // Vérifier que 5 secondes minimum se sont écoulées
        $timeElapsed = $currentTime - $formTimestamp;
        if ($timeElapsed < 5000) {
            $this->addFlash('error', 'Veuillez patienter avant d\'envoyer le formulaire.');
            return $this->redirectToRoute('index');
        }

        // Vérifier que ce n'est pas trop ancien (30 minutes max)
        if ($timeElapsed > 1800000) {
            $this->addFlash('error', 'Le formulaire a expiré. Veuillez le remplir à nouveau.');
            return $this->redirectToRoute('index');
        }

        // 2. PROTECTION HONEYPOT
        if (!empty($request->request->get('website'))) {
            // Log optionnel pour surveillance
            // $this->logger->warning('Bot détecté via honeypot', ['ip' => $request->getClientIp()]);
            $this->addFlash('error', 'Erreur détectée.');
            return $this->redirectToRoute('index');
        }

        // 3. VÉRIFICATION DU TOKEN CSRF
        if (!$this->isCsrfTokenValid('contact_form', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('index');
        }

        // 4. RÉCUPÉRATION DES DONNÉES
        $nom = trim($request->request->get('nom', ''));
        $email = trim($request->request->get('email', ''));
        $telephone = trim($request->request->get('telephone', ''));
        $sujet = $request->request->get('sujet', '');
        $message = trim($request->request->get('message', ''));

        // 5. VALIDATION BASIQUE
        if (empty($nom) || empty($email) || empty($message) || empty($sujet)) {
            $this->addFlash('error', 'Veuillez remplir tous les champs obligatoires.');
            return $this->redirectToRoute('index');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'Email invalide.');
            return $this->redirectToRoute('index');
        }

        // Validation supplémentaire des longueurs
        if (strlen($nom) < 2 || strlen($nom) > 100) {
            $this->addFlash('error', 'Le nom doit contenir entre 2 et 100 caractères.');
            return $this->redirectToRoute('index');
        }

        if (strlen($message) < 10 || strlen($message) > 2000) {
            $this->addFlash('error', 'Le message doit contenir entre 10 et 2000 caractères.');
            return $this->redirectToRoute('index');
        }


        $sujets = [
            'pension' => '🏡 Pension pour chevaux',
            'cours' => '🎓 Cours d\'équitation',
            'evenements' => '🎪 Événements et stages',
            'anniversaire' => '🎂 Anniversaire & Goûters',
            'visite' => '👁️ Visite des installations',
            'autre' => '❓ Autre demande'
        ];

        // Vérifier que le sujet est valide
        if (!array_key_exists($sujet, $sujets)) {
            $this->addFlash('error', 'Sujet invalide.');
            return $this->redirectToRoute('index');
        }

        try {
            // 6. PRÉPARATION ET ENVOI DE L'EMAIL
            $emailMessage = (new Email())
                ->from('support@ecuriesdes4routes.fr')
                ->to('ecuriesdes4routes@gmail.com')
                ->replyTo($email)
                ->subject('✉️ Contact: ' . $sujets[$sujet])
                //mail que les propriétaires vont recevoir
                ->html("
                <h2>Nouveau message de contact</h2>
                <div style='background:#f8f9fa;padding:20px;border-radius:8px;margin:20px 0;'>
                    <p><strong>📧 De:</strong> {$nom} ({$email})</p>
                    <p><strong>📞 Téléphone:</strong> " . ($telephone ?: 'Non renseigné') . "</p>
                    <p><strong>🏷️ Sujet:</strong> {$sujets[$sujet]}</p>
                    <p><strong>⏰ Reçu le:</strong> " . (new \DateTime())->format('d/m/Y à H:i') . "</p>
                </div>
                <div style='background:#e9ecef;padding:20px;border-radius:8px;border-left:4px solid #007bff;'>
                    <strong>💬 Message:</strong><br><br>
                    " . nl2br(htmlspecialchars($message)) . "
                </div>
                <hr style='margin:30px 0;'>
                <p style='font-size:12px;color:#6c757d;'>
                    <strong>🛡️ Informations de sécurité:</strong><br>
                    • Temps de remplissage: " . round($timeElapsed / 1000, 1) . " secondes<br>
                    • IP: {$request->getClientIp()}<br>
                    • User-Agent: " . substr($request->headers->get('User-Agent', 'Inconnu'), 0, 100) . "
                </p>
            ");

            $mailer->send($emailMessage);


            $this->addFlash('success', '✅ Message envoyé avec succès ! Nous vous répondrons rapidement.');

        } catch (\Exception $e) {


            $this->addFlash('error', '❌ Erreur lors de l\'envoi. Réessayez plus tard.');
        }

        return $this->redirectToRoute('index');
    }
}

