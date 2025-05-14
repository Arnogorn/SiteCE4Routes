<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;
    public function boot(): void
    {
        parent::boot();
        // Définir le fuseau horaire directement ici
        date_default_timezone_set('Europe/Paris');
    }

    public function setTimezone(string $timezone): void
    {
        // Cette méthode est peut-être appelée trop tard dans le cycle de vie de l'application
        // ou n'est pas appelée du tout
        date_default_timezone_set($timezone);
    }
}
