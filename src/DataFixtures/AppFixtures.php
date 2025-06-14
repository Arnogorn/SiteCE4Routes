<?php

namespace App\DataFixtures;

use App\Entity\Contact;
use App\Entity\EcurieProprietaire;
use App\Entity\Sexe;
use App\Entity\Tarifs;
use App\Entity\TypeSortie;
use App\Entity\User;
use App\Entity\Famille;
use App\Entity\MembreFamille;
use App\Entity\Niveau;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager): Void
    {
        // NIVEAUX

        // ETATS
        $etatLabels = ['Créée', 'Ouverte', 'Clôturée', 'En cours', 'Passée', 'Annulée', 'Archivée'];
        $etats = [];
        foreach ($etatLabels as $label) {
            $etat = new \App\Entity\Etat();
            $etat->setLibelle($label);
            $manager->persist($etat);
            $manager->flush();
            $etats[] = $etat;
        }

        $niveauLabels = [
            'Débutant', 'Intermédiaire', 'Confirmé',
            'Galop 1', 'Galop 2', 'Galop 3', 'Galop 4',
            'Galop 5', 'Galop 6', 'Galop 7', 'Galop 8',
            'Tous Niveaux'
        ];

        $niveaux = [];
        foreach ($niveauLabels as $libelle) {
            $niveau = new Niveau();
            $niveau->setLibelle($libelle);
            $manager->persist($niveau);
            $manager->flush();
            $niveaux[] = $niveau;
        }

        // === USERS INDIVIDUELS ===
        $plainPassword = 'M269RS!a';



        $user1 = (new User());


        $hashedPassword = $this->passwordHasher->hashPassword(
            $user1,
            $plainPassword
        );

        $user1 ->setPassword($hashedPassword) // password
            ->setEmail("ecuriesdes4routes@gmail.com")
            ->setRoles(['ROLE_ADMIN'])
            ->setNom("Admin")
            ->setPrenom("Istrateur")
            ->setDateNaissance(new \DateTimeImmutable('1990-06-05'))
            ->setAdresse("217 Les Quatre Routes, 35750 Iffendic")
            ->setTelephone("0698737600")
            ->setTelPersContact("0600000001")
            ->setAllergies("Aucune")
            ->setMedecinTraitant("Dr Lemoine")
            ->setTelMedecin("0612345678")
            ->setDroitImage(true)
            ->setActif(true)
            ->setIsVerified(true)
            ->setNiveau($niveaux[2]);




            $manager->persist($user1);
            $manager->flush();


        //SEXE

        $sexe1 = new Sexe();
        $sexe1->setLibelle("Hongre");
        $sexe2 = new Sexe();
        $sexe2->setLibelle("Jument");
        $sexe3 = new Sexe();
        $sexe3->setLibelle("Étalon");

        $manager->persist($sexe1);
        $manager->persist($sexe2);
        $manager->persist($sexe3);
        $manager->flush();






        //écurie propriétaire
    $ecurieproprio1 = (new ecurieProprietaire());
    $ecurieproprio1->setPrestation("Pension travail")
        ->setDescription("Nourris 3 fois par jour + 2 repas de foin.
        Travail 5 fois par semaine ou participation aux cours + sortie paddock ou marcheur")
        ->setPrix("483");
    $manager->persist($ecurieproprio1);
    $manager->flush();

    $ecurieproprio2 = (new ecurieProprietaire());
    $ecurieproprio2->setPrestation("Pension club")
        ->setDescription("travail maximum 5 fois par semaine en club,
        boxe maréchal et vermifuge compris")
        ->setPrix("300");

    $manager->persist($ecurieproprio2);
    $manager->flush();

    $ecurieproprio3 = (new ecurieProprietaire());
    $ecurieproprio3->setPrestation("Demi pension")
        ->setDescription("3 séances montées dont 1 cours comprit par semaine")
        ->setPrix("150");
    $manager->persist($ecurieproprio3);
    $manager->flush();

// Poney club

        $poneyClub1 = (new tarifs());
        $poneyClub1->setCategorie("licences")
            ->setDescription("Licence - 18 ans")
            ->setPrix("25");
        $manager->persist($poneyClub1);
        $manager->flush();

        $poneyClub2 = (new tarifs());
        $poneyClub2->setCategorie("licences")
            ->setDescription("Licence + 18 ans")
            ->setPrix("36");
        $manager->persist($poneyClub2);
        $manager->flush();

        $poneyClub3 = (new tarifs());
        $poneyClub3->setCategorie("a_la_carte")
            ->setDescription("10 cours collectif")
            ->setPrix("245");
        $manager->persist($poneyClub3);
        $manager->flush();


        $typeSortie1 = (new typeSortie());
        $typeSortie1->setLibelle("Randonnée");
        $manager->persist($typeSortie1);
        $manager->flush();

        $typeSortie2 = (new typeSortie());
        $typeSortie2->setLibelle("Stage");
        $manager->persist($typeSortie2);
        $manager->flush();


    }


}