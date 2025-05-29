<?php

namespace App\Controller;

use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route(path: '/', name: 'index', methods: ['GET'])]
    function index(ContactRepository $contactRepository): Response
    {
        $contact = $contactRepository->findAll();
        return $this->render('main/index.html.twig', [
            'contacts' => $contact,
        ]);
    }

    #[Route('/send-contact', name: 'send_contact', methods: ['POST'])]
    public function sendContactSimple(Request $request, MailerInterface $mailer): Response
    {
        // Protection honeypot
        if (!empty($request->request->get('website'))) {
            $this->addFlash('error', 'Erreur détectée.');
            return $this->redirectToRoute('index');
        }

        // Récupération des données
        $nom = trim($request->request->get('nom', ''));
        $email = trim($request->request->get('email', ''));
        $telephone = trim($request->request->get('telephone', ''));
        $sujet = $request->request->get('sujet', '');
        $message = trim($request->request->get('message', ''));

        // Validation basique
        if (empty($nom) || empty($email) || empty($message) || empty($sujet)) {
            $this->addFlash('error', 'Veuillez remplir tous les champs obligatoires.');
            return $this->redirectToRoute('index');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'Email invalide.');
            return $this->redirectToRoute('index');
        }

        $sujets = [
            'pension' => '🏡 Pension pour chevaux',
            'cours' => '🎓 Cours d\'équitation',
            'evenements' => '🎪 Événements et stages',
            'visite' => '👁️ Visite des installations',
            'autre' => '❓ Autre demande'
        ];

        try {
            // Email simple et efficace
            $emailMessage = (new Email())
                ->from('noreply@votre-site.com') // Changez par votre email
                ->to('contact@votre-site.com')    // Changez par votre email
                ->subject('✉️ Contact: ' . $sujets[$sujet])
                ->html("
                    <h2>Nouveau message de contact</h2>
                    <p><strong>De:</strong> {$nom} ({$email})</p>
                    <p><strong>Téléphone:</strong> " . ($telephone ?: 'Non renseigné') . "</p>
                    <p><strong>Sujet:</strong> {$sujets[$sujet]}</p>
                    <div style='background:#f0f0f0;padding:15px;border-radius:5px;'>
                        <strong>Message:</strong><br>
                        " . nl2br(htmlspecialchars($message)) . "
                    </div>
                ");

            $mailer->send($emailMessage);
            $this->addFlash('success', '✅ Message envoyé avec succès ! Nous vous répondrons rapidement.');

        } catch (\Exception $e) {
            $this->addFlash('error', '❌ Erreur lors de l\'envoi. Réessayez plus tard.');
        }

        return $this->redirectToRoute('index');
    }
}

