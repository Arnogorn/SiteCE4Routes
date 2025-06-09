<?php

namespace App\Controller;

use App\Repository\HistoriquePaiementRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HistoriquePaiementController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/historique', name: 'historique_index')]
    public function index(HistoriquePaiementRepository $repository): Response
    {
        $user = $this->getUser();

        // On récupère tous les historiques de paiements de l'utilisateur connecté
        $logs = $repository->findByUser($user);

        return $this->render('historique_paiement/index.html.twig', [
            'logs' => $logs,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/paiement', name: 'admin_index_paiement')]
    public function admin(Request $request, HistoriquePaiementRepository $repository): Response
    {
        // Récupération des filtres avec valeurs par défaut
        $filters = [
            'email' => $request->query->get('email', ''),
            'nom' => $request->query->get('nom', ''),
            'type' => $request->query->get('type', ''),
            'sortie' => $request->query->get('sortie', ''),
            'date_debut' => $request->query->get('date_debut', ''),
            'date_fin' => $request->query->get('date_fin', ''),
        ];

        // Suppression des filtres vides pour la requête
        $filtersForQuery = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });

        // Recherche avec les filtres
        $logs = $repository->findByFilters($filtersForQuery);

        // Statistiques
        $statistiques = $repository->getStatistiques();

        return $this->render('historique_paiement/admin.html.twig', [
            'logs' => $logs,
            'filters' => $filters, // On passe le tableau complet avec toutes les clés
            'statistiques' => $statistiques,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/historique/admin/export', name: 'historique_export_csv')]
    public function exportCsv(Request $request, HistoriquePaiementRepository $repository): StreamedResponse
    {
        // Récupération des filtres identiques à la méthode admin
        $filters = [
            'email' => $request->query->get('email'),
            'nom' => $request->query->get('nom'),
            'type' => $request->query->get('type'),
            'sortie' => $request->query->get('sortie'),
            'date_debut' => $request->query->get('date_debut'),
            'date_fin' => $request->query->get('date_fin'),
        ];

        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });

        $logs = $repository->findByFilters($filters);

        $response = new StreamedResponse();
        $response->setCallback(function() use ($logs) {
            $handle = fopen('php://output', 'w+');

            // En-têtes CSV avec BOM UTF-8 pour Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // En-têtes des colonnes
            fputcsv($handle, [
                'Date',
                'Email utilisateur',
                'Nom',
                'Prénom',
                'Type de paiement',
                'Sortie',
                'Message'
            ], ';');

            // Données
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->getDate()->format('d/m/Y H:i:s'),
                    $log->getUtilisateur() ? $log->getUtilisateur()->getEmail() : 'N/A',
                    $log->getUtilisateur() ? $log->getUtilisateur()->getNom() : 'N/A',
                    $log->getUtilisateur() ? $log->getUtilisateur()->getPrenom() : 'N/A',
                    $log->getType(),
                    $log->getSortie() ? $log->getSortie()->getTitre() : 'N/A',
                    $log->getMessage() ?? 'Aucun message'
                ], ';');
            }

            fclose($handle);
        });

        $filename = 'historique_paiements_' . date('Y-m-d_H-i-s') . '.csv';

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/historique/admin/stats', name: 'historique_admin_stats')]
    public function stats(HistoriquePaiementRepository $repository): Response
    {
        $statistiques = $repository->getStatistiques();
        $statsParType = $repository->getStatistiquesByType();
        $paiementsRecents = $repository->findRecent(5);

        return $this->render('historique_paiement/stats.html.twig', [
            'statistiques' => $statistiques,
            'stats_par_type' => $statsParType,
            'paiements_recents' => $paiementsRecents,
        ]);
    }
}