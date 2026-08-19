<?php

namespace App\Twig;

use App\Entity\PhotoSite;
use App\Repository\PhotoSiteRepository;
use App\Service\PhotoSiteRegistry;
use Symfony\Component\Asset\Packages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PhotoSiteExtension extends AbstractExtension
{
    /** @var array<string, PhotoSite>|null */
    private ?array $overridesBySlug = null;

    public function __construct(
        private readonly PhotoSiteRepository $photoSiteRepository,
        private readonly PhotoSiteRegistry $photoSiteRegistry,
        private readonly Packages $packages,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('photo_site', [$this, 'resolveUrl']),
            new TwigFunction('photo_site_title', [$this, 'resolveTitle']),
            new TwigFunction('photo_site_description', [$this, 'resolveDescription']),
        ];
    }

    public function resolveUrl(string $slug): string
    {
        $filename = $this->getOverride($slug)?->getFilename();

        if ($filename !== null) {
            return $this->packages->getUrl('images/uploads/site/' . $filename);
        }

        return $this->packages->getUrl('images/' . $this->photoSiteRegistry->defaultFilename($slug));
    }

    public function resolveTitle(string $slug): string
    {
        return $this->getOverride($slug)?->getTitle() ?? $this->photoSiteRegistry->defaultTitle($slug);
    }

    public function resolveDescription(string $slug): string
    {
        return $this->getOverride($slug)?->getDescription() ?? $this->photoSiteRegistry->defaultDescription($slug);
    }

    private function getOverride(string $slug): ?PhotoSite
    {
        if ($this->overridesBySlug === null) {
            $this->overridesBySlug = $this->photoSiteRepository->findAllIndexedBySlug();
        }

        return $this->overridesBySlug[$slug] ?? null;
    }
}
