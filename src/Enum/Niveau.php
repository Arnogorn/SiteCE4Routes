<?php

namespace App\Enum;

enum Niveau : int
{

    case DEBUTANT = 0;
    case GALOP1 = 1;
    case GALOP2 = 2;
    case GALOP3 = 3;
    case CONFIRME = 4;
    case GALOP4 = 5;
    case GALOP5 = 6;
    case GALOP6 = 7;
    case GALOP7 = 8;
    case GALOP8 = 9;

    case TOUS_NIVEAUX = -1; // Même valeur que DEBUTANT car accessible à tous

    public function libelle(): string
    {
        return match($this) {
            self::DEBUTANT => 'Débutant',
            self::GALOP1 => 'Galop 1',
            self::GALOP2 => 'Galop 2',
            self::GALOP3 => 'Galop 3',
            self::GALOP4 => 'Galop 4',
            self::GALOP5 => 'Galop 5',
            self::GALOP6 => 'Galop 6',
            self::GALOP7 => 'Galop 7',
            self::GALOP8 => 'Galop 8',
            self::CONFIRME => 'Confirmé',
            self::TOUS_NIVEAUX => 'Tous niveaux',
        };
    }

    public static function fromString(string $value): self
    {
        return match(strtolower($value)) {
            'débutant', 'debutant' => self::DEBUTANT,
            'galop 1', 'galop1' => self::GALOP1,
            'galop 2', 'galop2' => self::GALOP2,
            'galop 3', 'galop3' => self::GALOP3,
            'galop 4', 'galop4' => self::GALOP4,
            'galop 5', 'galop5' => self::GALOP5,
            'galop 6', 'galop6' => self::GALOP6,
            'galop 7', 'galop7' => self::GALOP7,
            'galop 8', 'galop8' => self::GALOP8,
            'confirmé', 'confirme' => self::CONFIRME,
            'tous niveaux', 'tous_niveaux' => self::TOUS_NIVEAUX,
            default => throw new \InvalidArgumentException("Niveau non reconnu: $value"),
        };
    }
}

