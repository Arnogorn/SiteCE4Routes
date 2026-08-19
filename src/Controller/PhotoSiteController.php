<?php

namespace App\Controller;

use App\Entity\PhotoSite;
use App\Form\PhotoSiteEditType;
use App\Repository\PhotoSiteRepository;
use App\Service\PhotoSiteRegistry;
use App\Service\Uploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/photos')]
class PhotoSiteController extends AbstractController
{
    #[Route('', name: 'app_photo_site_index', methods: ['GET'])]
    public function index(PhotoSiteRepository $photoSiteRepository, PhotoSiteRegistry $photoSiteRegistry): Response
    {
        $existing = $photoSiteRepository->findAllIndexedBySlug();

        $groupsByPage = [];
        $forms = [];

        foreach ($photoSiteRegistry->all() as $slug => $meta) {
            $photoSite = $existing[$slug] ?? null;

            $groupsByPage[$meta['page']][] = [
                'slug' => $slug,
                'label' => $meta['label'],
                'photoSite' => $photoSite,
            ];

            $forms[$slug] = $this->createForm(PhotoSiteEditType::class, [
                'title' => $photoSite?->getTitle() ?? $meta['title'],
                'description' => $photoSite?->getDescription() ?? $meta['description'],
            ], [
                'action' => $this->generateUrl('app_photo_site_edit', ['slug' => $slug]),
            ])->createView();
        }

        return $this->render('photo_site/index.html.twig', [
            'groupsByPage' => $groupsByPage,
            'forms' => $forms,
        ]);
    }

    #[Route('/{slug}/modifier', name: 'app_photo_site_edit', methods: ['POST'])]
    public function edit(
        string $slug,
        Request $request,
        PhotoSiteRepository $photoSiteRepository,
        PhotoSiteRegistry $photoSiteRegistry,
        EntityManagerInterface $entityManager,
        Uploader $uploader,
    ): Response {
        if (!$photoSiteRegistry->exists($slug)) {
            throw $this->createNotFoundException('Emplacement photo inconnu.');
        }

        $form = $this->createForm(PhotoSiteEditType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('danger', "La modification n'a pas pu être enregistrée (photo, légende ou texte invalide).");
            return $this->redirectToRoute('app_photo_site_index');
        }

        $photoSite = $photoSiteRepository->findOneBySlug($slug);
        if ($photoSite === null) {
            $meta = $photoSiteRegistry->all()[$slug];
            $photoSite = (new PhotoSite())
                ->setSlug($slug)
                ->setLabel($meta['page'] . ' - ' . $meta['label']);
            $entityManager->persist($photoSite);
        }

        $title = trim((string) $form->get('title')->getData());
        $description = trim((string) $form->get('description')->getData());
        $photoSite->setTitle($title === '' ? null : $title);
        $photoSite->setDescription($description === '' ? null : $description);

        $picture = $form->get('photo')->getData();
        if ($picture) {
            if ($photoSite->getFilename() !== null) {
                $uploader->delete($photoSite->getFilename(), $this->getParameter('photo_site_picture_dir'));
            }
            $fileName = $uploader->save($picture, $slug, $this->getParameter('photo_site_picture_dir'), 1600, 1200, false);
            $photoSite->setFilename($fileName);
        }

        $photoSite->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->flush();

        $this->addFlash('success', 'Photo mise à jour.');

        return $this->redirectToRoute('app_photo_site_index');
    }

    #[Route('/{slug}/reinitialiser', name: 'app_photo_site_reset', methods: ['POST'])]
    public function reset(
        string $slug,
        Request $request,
        PhotoSiteRepository $photoSiteRepository,
        PhotoSiteRegistry $photoSiteRegistry,
        EntityManagerInterface $entityManager,
        Uploader $uploader,
    ): Response {
        if (!$photoSiteRegistry->exists($slug)) {
            throw $this->createNotFoundException('Emplacement photo inconnu.');
        }

        if (!$this->isCsrfTokenValid('reset_photo_' . $slug, $request->getPayload()->getString('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_photo_site_index');
        }

        $photoSite = $photoSiteRepository->findOneBySlug($slug);
        if ($photoSite !== null) {
            if ($photoSite->getFilename() !== null) {
                $uploader->delete($photoSite->getFilename(), $this->getParameter('photo_site_picture_dir'));
                $photoSite->setFilename(null);
            }
            $photoSite->setTitle(null);
            $photoSite->setDescription(null);
            $photoSite->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();
        }

        $this->addFlash('success', "Photo, légende et texte d'origine restaurés.");

        return $this->redirectToRoute('app_photo_site_index');
    }
}
