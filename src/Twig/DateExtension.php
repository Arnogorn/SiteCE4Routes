<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DateExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('current_season', [$this, 'getCurrentSeason']),
            new TwigFunction('current_year', [$this, 'getCurrentYear']),
            new TwigFunction('next_year', [$this, 'getNextYear']),
        ];
    }

    /**
     * Retourne la saison actuelle au format "2025/2026"
     */
    public function getCurrentSeason(): string
    {
        $currentYear = (int) date('Y');
        $nextYear = $currentYear + 1;
        return "{$currentYear}/{$nextYear}";
    }

    /**
     * Retourne l'année actuelle
     */
    public function getCurrentYear(): int
    {
        return (int) date('Y');
    }

    /**
     * Retourne l'année suivante
     */
    public function getNextYear(): int
    {
        return (int) date('Y') + 1;
    }
}