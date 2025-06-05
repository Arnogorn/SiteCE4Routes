<?php

namespace App\Service;

class CitationQuotidienneService
{

    private array $quotes = [
        1 => [ // Lundi
            'text' => "Il n'y a pas de secret aussi intime que celui d'un cavalier et de son cheval.",
            'author' => "Robert Smith Surtees"
        ],
        2 => [ // Mardi
            'text' => "Le cheval enseigne à l'homme la maîtrise de soi, et la faculté de s'introduire dans les pensées et les sensations d'un autre être vivant.",
            'author' => "Alois Podhajsky"
        ],
        3 => [ // Mercredi
            'text' => "Un cheval ! Un cheval ! Mon royaume pour un cheval !",
            'author' => "William Shakespeare"
        ],
        4 => [ // Jeudi
            'text' => "Le cheval est la plus noble conquête que l'homme ait jamais faite.",
            'author' => "Georges-Louis Leclerc de Buffon"
        ],
        5 => [ // Vendredi
            'text' => "Il n'y a rien de plus beau qu'un cheval au galop dans un pré.",
            'author' => "Jean Giono"
        ],
        6 => [ // Samedi
            'text' => "Les chevaux prêtent à l'homme la rapidité et la force qui lui manquent, mais l'homme rend au cheval la direction et la dignité qui lui manquent.",
            'author' => "Joseph Joubert"
        ],
        0 => [ // Dimanche (0 = dimanche en PHP)
            'text' => "On peut juger de la grandeur d'une nation par la façon dont les animaux y sont traités.",
            'author' => "Mahatma Gandhi"
        ]
    ];

    public function getTodayQuote(): array
    {
        $dayOfWeek = (int) date('w'); // 0 = dimanche, 1 = lundi, etc...

        return $this->quotes[$dayOfWeek] ?? $this->quotes[1];
    }
}