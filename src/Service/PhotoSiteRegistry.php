<?php

namespace App\Service;

/**
 * Source de vérité des photos "vitrine" modifiables par l'admin : chaque emplacement
 * du site (hors bandeau d'accueil, géré séparément) est identifié par un slug stable,
 * avec l'image, la légende et le texte d'origine du site utilisés tant que l'admin
 * n'a rien personnalisé.
 */
class PhotoSiteRegistry
{
    private const PHOTOS = [
        'accueil_dressage' => [
            'page' => 'Accueil',
            'label' => 'Excellence en dressage',
            'default' => 'concours_3.jpg',
            'title' => 'Excellence en dressage',
            'description' => "La précision et l'harmonie en compétition",
        ],
        'accueil_obstacles' => [
            'page' => 'Accueil',
            'label' => "Saut d'obstacles en compétitions",
            'default' => 'accueil_3.jpg',
            'title' => "Saut d'obstacles en compétition",
            'description' => 'Entraînement et perfectionnement de Damien pour les compétitions',
        ],
        'ecurie_manege' => [
            'page' => 'Écurie propriétaire',
            'label' => 'Manège couvert',
            'default' => 'ecuries_1.jpg',
            'title' => 'Manège couvert',
            'description' => 'Notre grand manège couvert pour travailler par tous les temps',
        ],
        'ecurie_marcheur' => [
            'page' => 'Écurie propriétaire',
            'label' => 'Marcheur automatique',
            'default' => 'ecuries_2.jpg',
            'title' => 'Marcheur automatique',
            'description' => "Notre marcheur pour l'exercice et la détente de votre cheval",
        ],
        'commerce_dressage' => [
            'page' => 'Commerce',
            'label' => 'Dressage en compétition avec Lorraine',
            'default' => 'commerce_1.jpg',
            'title' => 'Dressage avec Lorraine',
            'description' => 'Excellence et élégance de nos chevaux',
        ],
        'commerce_obstacles' => [
            'page' => 'Commerce',
            'label' => "Saut d'obstacles avec Damien",
            'default' => 'commerce_2.jpg',
            'title' => "Saut d'obstacles avec Damien",
            'description' => 'Performance et agilité en compétition',
        ],
        'contact_obstacles' => [
            'page' => 'Contact',
            'label' => "Saut d'obstacles en compétition avec Damien",
            'default' => 'equipe_1.jpg',
            'title' => "Saut d'obstacles",
            'description' => 'Excellence et technique en concours avec Damien',
        ],
        'contact_dressage' => [
            'page' => 'Contact',
            'label' => 'Dressage en compétition avec Lorraine',
            'default' => 'equipe_2.jpg',
            'title' => 'Dressage',
            'description' => 'Précision et harmonie en compétition avec Lorraine',
        ],
        'competition_obstacles' => [
            'page' => 'Compétition',
            'label' => "Saut d'obstacles en compétition",
            'default' => 'concours_1.jpg',
            'title' => "Saut d'obstacles",
            'description' => 'Performances de haut niveau en compétition',
        ],
        'competition_ambiance' => [
            'page' => 'Compétition',
            'label' => 'Ambiance des concours',
            'default' => 'concours_2.jpg',
            'title' => 'Ambiance des concours',
            'description' => 'Convivialité et esprit sportif',
        ],
        'tarifs_depart' => [
            'page' => 'Tarifs / Poney club',
            'label' => 'Préparation et départ à cheval',
            'default' => 'poney_club_3.jpg',
            'title' => 'Préparation et départ',
            'description' => 'Nos cavaliers se préparent pour un cours équestre',
        ],
        'tarifs_manege' => [
            'page' => 'Tarifs / Poney club',
            'label' => "Cours d'équitation en manège",
            'default' => 'poney_club_2.jpg',
            'title' => 'Cours en manège',
            'description' => 'Apprentissage dans un cadre sécurisé',
        ],
        'sortie_lac' => [
            'page' => 'Sorties',
            'label' => 'Balade au lac de Trémelin',
            'default' => 'activites_1.jpeg',
            'title' => "Balade au bord de l'eau",
            'description' => 'Nos sorties découverte dans des paysages magnifiques',
        ],
        'sortie_foret' => [
            'page' => 'Sorties',
            'label' => 'Balade en forêt',
            'default' => 'activites_2.jpg',
            'title' => 'Balade en forêt',
            'description' => 'Promenades à travers les sentiers verdoyants',
        ],
    ];

    /**
     * @return array<string, array{page: string, label: string, default: string, title: string, description: string}>
     */
    public function all(): array
    {
        return self::PHOTOS;
    }

    public function exists(string $slug): bool
    {
        return isset(self::PHOTOS[$slug]);
    }

    /**
     * @return array{page: string, label: string, default: string, title: string, description: string}
     */
    public function get(string $slug): array
    {
        if (!$this->exists($slug)) {
            throw new \InvalidArgumentException(sprintf('Slug de photo inconnu : "%s".', $slug));
        }

        return self::PHOTOS[$slug];
    }

    public function defaultFilename(string $slug): string
    {
        return $this->get($slug)['default'];
    }

    public function defaultTitle(string $slug): string
    {
        return $this->get($slug)['title'];
    }

    public function defaultDescription(string $slug): string
    {
        return $this->get($slug)['description'];
    }
}
