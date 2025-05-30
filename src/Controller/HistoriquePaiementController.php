<?php

namespace App\Controller;

use App\Repository\HistoriquePaiementRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/historique', name: 'historique_')]
class HistoriquePaiementController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/', name: 'index')]
    public function index(HistoriquePaiementRepository $repository): Response
    {
        $user = $this->getUser();

        // On récupère tous les historiques de paiements de l'utilisateur connecté
        $logs = $repository->findBy(['utilisateur' => $user], ['date' => 'DESC']);

        return $this->render('historique_paiement/index.html.twig', [
            'logs' => $logs,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin', name: 'admin_index')]
    public function admin(Request $request, HistoriquePaiementRepository $repository): Response
    {
        $filters = [];
        $utilisateurId = $request->query->get('utilisateur');
        $type = $request->query->get('type');

        if ($utilisateurId) {
            $filters['utilisateur'] = $utilisateurId;
        }

        if ($type) {
            $filters['type'] = $type;
        }

        $logs = $repository->findBy($filters, ['date' => 'DESC']);

        return $this->render('historique_paiement/admin.html.twig', [
            'logs' => $logs,
            'filters' => [
                'utilisateur' => $utilisateurId,
                'type' => $type,
            ],
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/export', name: 'export_csv')]
    public function exportCsv(Request $request, HistoriquePaiementRepository $repository): Response
    {
        $filters = [];
        $utilisateurId = $request->query->get('utilisateur');
        $type = $request->query->get('type');

        if ($utilisateurId) {
            $filters['utilisateur'] = $utilisateurId;
        }

        if ($type) {
            $filters['type'] = $type;
        }

        $logs = $repository->findBy($filters, ['date' => 'DESC']);

        $csvData = [];
        $csvData[] = ['Date', 'Utilisateur', 'Type', 'Sortie', 'Message'];

        foreach ($logs as $log) {
            $csvData[] = [
                $log->getDate()->format('Y-m-d H:i:s'),
                $log->getUtilisateur()?->getEmail(),
                $log->getType(),
                $log->getSortie()?->getTitre() ?? '',
                $log->getMessage(),
            ];
        }

        $handle = fopen('php://temp', 'r+');
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return new Response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="historique_paiements.csv"',
        ]);
    }
}