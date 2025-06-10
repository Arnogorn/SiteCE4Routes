<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/inscriptionPDF')]
class InscriptionPdfController extends AbstractController
{
    private string $uploadsDirectory;

    public function __construct(string $uploadsDirectory)
    {
        $this->uploadsDirectory = $uploadsDirectory;
    }

    /**
     * Téléchargement du PDF d'inscription par les utilisateurs
     */
    #[Route('/download-pdf', name: 'app_download_inscription_pdf', methods: ['GET'])]
    public function downloadPdf(): Response
    {
        $currentYear = (int) date('Y');
        $nextYear = $currentYear + 1;
        $filename = "fiche-inscription-{$currentYear}-{$nextYear}.pdf";
        $filePath = $this->uploadsDirectory . '/inscription/' . $filename;

        // Vérifier si le fichier existe
        if (!file_exists($filePath)) {
            $this->addFlash('error', 'Le fichier d\'inscription n\'est pas disponible pour le moment.');
            return $this->redirectToRoute('app_tarifs_index');
        }

        // Retourner le fichier en téléchargement
        return $this->file($filePath, $filename);
    }

    /**
     * Page d'administration pour gérer le PDF d'inscription
     */
    #[Route('/admin/manage-pdf', name: 'app_admin_inscription_pdf', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function managePdf(Request $request, SluggerInterface $slugger): Response
    {
        $currentYear = (int) date('Y');
        $nextYear = $currentYear + 1;
        $filename = "fiche-inscription-{$currentYear}-{$nextYear}.pdf";
        $filePath = $this->uploadsDirectory . '/inscription/' . $filename;

        $fileExists = file_exists($filePath);
        $fileInfo = null;

        if ($fileExists) {
            $fileInfo = [
                'name' => $filename,
                'size' => $this->formatBytes(filesize($filePath)),
                'date' => date('d/m/Y H:i', filemtime($filePath))
            ];
        }

        if ($request->isMethod('POST')) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $request->files->get('pdf_file');

            if ($uploadedFile) {
                // Validation du fichier
                if ($uploadedFile->getMimeType() !== 'application/pdf') {
                    $this->addFlash('error', 'Le fichier doit être au format PDF.');
                    return $this->redirectToRoute('app_admin_inscription_pdf');
                }

                // Taille maximale : 10MB
                if ($uploadedFile->getSize() > 10 * 1024 * 1024) {
                    $this->addFlash('error', 'Le fichier ne doit pas dépasser 10MB.');
                    return $this->redirectToRoute('app_admin_inscription_pdf');
                }

                try {
                    // Créer le dossier s'il n'existe pas
                    $uploadDir = $this->uploadsDirectory . '/inscription';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    // Supprimer l'ancien fichier s'il existe
                    if ($fileExists) {
                        unlink($filePath);
                    }

                    // Déplacer le nouveau fichier
                    $uploadedFile->move($uploadDir, $filename);

                    $this->addFlash('success', 'Le fichier PDF d\'inscription a été mis à jour avec succès.');
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléversement du fichier.');
                }
            } else {
                $this->addFlash('error', 'Veuillez sélectionner un fichier PDF.');
            }

            return $this->redirectToRoute('app_admin_inscription_pdf');
        }

        return $this->render('admin/inscription_pdf.html.twig', [
            'current_year' => $currentYear,
            'next_year' => $nextYear,
            'file_exists' => $fileExists,
            'file_info' => $fileInfo,
        ]);
    }

    /**
     * Suppression du PDF d'inscription (admin uniquement)
     */
    #[Route('/admin/delete-pdf', name: 'app_admin_delete_inscription_pdf', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deletePdf(Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete_inscription_pdf', $request->request->get('_token'))) {
            $currentYear = (int) date('Y');
            $nextYear = $currentYear + 1;
            $filename = "fiche-inscription-{$currentYear}-{$nextYear}.pdf";
            $filePath = $this->uploadsDirectory . '/inscription/' . $filename;

            if (file_exists($filePath)) {
                unlink($filePath);
                $this->addFlash('success', 'Le fichier PDF d\'inscription a été supprimé.');
            } else {
                $this->addFlash('error', 'Le fichier n\'existe pas.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_admin_inscription_pdf');
    }

    /**
     * Formatage de la taille des fichiers
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Fonction helper pour obtenir la saison actuelle (pour les templates)
     */
    public static function getCurrentSeason(): string
    {
        $currentYear = (int) date('Y');
        $nextYear = $currentYear + 1;
        return "{$currentYear}/{$nextYear}";
    }
}