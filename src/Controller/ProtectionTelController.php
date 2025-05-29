<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class ProtectionTelController extends AbstractController
{
    #[Route('/log-honeypot-click', name: 'log_honeypot_click')]
    public function logHoneypotClick(LoggerInterface $logger): Response
    {
        $logger->warning('Bot détecté: clic sur honeypot téléphonique', [
            'ip' => $this->container->get('request_stack')->getCurrentRequest()->getClientIp()
        ]);

        return new Response('', Response::HTTP_OK);
    }


}