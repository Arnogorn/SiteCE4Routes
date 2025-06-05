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
            $niveaux[] = $niveau;
        }

        // === USERS INDIVIDUELS ===
        $plainPassword = 'password';



        $user1 = (new User());


        $hashedPassword = $this->passwordHasher->hashPassword(
            $user1,
            $plainPassword
        );

        $user1 ->setPassword($hashedPassword) // password
            ->setEmail("admin@admin.fr")
            ->setRoles(['ROLE_ADMIN'])
            ->setNom("Admin")
            ->setPrenom("Istrateur")
            ->setDateNaissance(new \DateTimeImmutable('1990-05-14'))
            ->setAdresse("12 rue des Lilas, Lyon")
            ->setTelephone("0601020304")
            ->setTelPersContact("0600000001")
            ->setAllergies("Aucune")
            ->setMedecinTraitant("Dr Lemoine")
            ->setTelMedecin("0612345678")
            ->setDroitImage(true)
            ->setActif(true)
            ->setIsVerified(true)
            ->setNiveau($niveaux[0]);

        $user2 = (new User());

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user2,
            $plainPassword
        );
          $user2  ->setEmail("bob@bob.fr")
            ->setPassword($hashedPassword) // password
            ->setRoles(['ROLE_USER'])
            ->setNom("Martin")
            ->setPrenom("Bob")
            ->setDateNaissance(new \DateTimeImmutable('1988-11-23'))
            ->setAdresse("5 avenue du Stade, Toulouse")
            ->setTelephone("0611121314")
            ->setTelPersContact("0600000002")
            ->setAllergies("Pollen")
            ->setMedecinTraitant("Dr Bernard")
            ->setTelMedecin("0611111111")
            ->setDroitImage(true)
            ->setActif(true)
            ->setIsVerified(true)
            ->setNiveau($niveaux[1]);

        $user3 = (new User());

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user3,
            $plainPassword
        );
        $user3
            ->setEmail("carole@carole.fr")
            ->setPassword($hashedPassword) // password
            ->setRoles(['ROLE_USER'])
            ->setNom("Lemoine")
            ->setPrenom("Carole")
            ->setDateNaissance(new \DateTimeImmutable('1995-03-09'))
            ->setAdresse("7 chemin du Lac, Marseille")
            ->setTelephone("0621222324")
            ->setTelPersContact("0600000003")
            ->setAllergies("Aspirine")
            ->setMedecinTraitant("Dr Faure")
            ->setTelMedecin("0699999999")
            ->setDroitImage(false)
            ->setActif(true)
            ->setIsVerified(true)
            ->setNiveau($niveaux[2]);


        // MONITEURS
        $userM1 = (new User())
            ->setEmail("marc@moniteur.fr")
            ->setPassword('$2y$13$VxFzxhEoVQb6V5VVYClGke6E8EBwBPKyDxMjkIJOVjE8UI1T4Z8Wm') // password
            ->setRoles(['ROLE_USER'])
            ->setNom("Dupuis")
            ->setPrenom("Marc")
            ->setDateNaissance(new \DateTimeImmutable('1985-04-20'))
            ->setAdresse("2 rue des Écuries, Lyon")
            ->setTelephone("0677001100")
            ->setTelPersContact("0600000010")
            ->setAllergies("Aucune")
            ->setMedecinTraitant("Dr Brillant")
            ->setTelMedecin("0610101010")
            ->setDroitImage(true)
            ->setActif(true)
            ->setIsVerified(true)
            ->setNiveau($niveaux[1]);

        $moniteur1 = new \App\Entity\Moniteur();
        $moniteur1->setUser($userM1);

        $manager->persist($userM1);
        $manager->persist($moniteur1);


        // === FAMILLE ET MEMBRES ===

        $userFamille = (new User())
            ->setEmail("famille.durand@example.com")
            ->setPassword('$2y$13$VxFzxhEoVQb6V5VVYClGke6E8EBwBPKyDxMjkIJOVjE8UI1T4Z8Wm') // password
            ->setRoles(['ROLE_USER'])
            ->setNom("Durand")
            ->setPrenom("Claire")
            ->setDateNaissance(new \DateTimeImmutable('1975-08-20'))
            ->setAdresse("42 rue du Moulin, Nantes")
            ->setTelephone("0601020304")
            ->setTelPersContact("0600000101")
            ->setAllergies("Aucune")
            ->setMedecinTraitant("Dr Julien")
            ->setTelMedecin("0611223344")
            ->setDroitImage(true)
            ->setActif(true)
            ->setIsVerified(true)
            ->setNiveau($niveaux[0]);
//            ->setFamille($famille); Il n'y en a plus besoin pusque le constructeur créer automatiquement une famille lors de la création du User

        $famille = $userFamille->getFamille(); // récupère la famille créée automatiquement dans le constructeur

        $membre1 = (new MembreFamille())
            ->setNom("Durand")
            ->setPrenom("Léa")
            ->setDateNaissance(new \DateTimeImmutable('2010-05-15'))
            ->setDroitImage(true)
            ->setAllergies("Pollen")
            ->setMedecinTraitant("Dr Moulin")
            ->setTelMedecin("0699887766")
            ->setFamille($famille)
            ->setNiveau($niveaux[1]);

        $membre2 = (new MembreFamille())
            ->setNom("Durand")
            ->setPrenom("Tom")
            ->setDateNaissance(new \DateTimeImmutable('2012-09-10'))
            ->setDroitImage(false)
            ->setAllergies("Arachides")
            ->setMedecinTraitant("Dr Lemoine")
            ->setTelMedecin("0688776655")
            ->setFamille($famille)
            ->setNiveau($niveaux[2]);

        foreach ([$user1, $user2, $user3, $userFamille, $famille, $membre1, $membre2] as $entity) {
            $manager->persist($entity);
        }

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

        // CHEVAUX
        $cheval1 = new \App\Entity\Cheval();
        $cheval1->setNom("Orage")
            ->setNomPere("Tonnerre")
            ->setNomMere("Brume")
            ->setDateNaissance(new \DateTimeImmutable('2017-04-01'))
            ->setSexe($sexe3)
            ->setLieuNaissance("Élevage du Sud")
            ->setAppartientEcurie(false)
            ->setAVendre(false)
            ->setVendu(false)
            ->setInfos("Cheval très docile et idéal pour les débutants.")
            ->setUser($user1); // Alice

        $manager->persist($cheval1);

        // SORTIE


        // COMPÉTITION
        $competition1 = new \App\Entity\Competition();
        $competition1->setTitre("Concours d’Obstacle")
            ->setDateDebut(new \DateTimeImmutable('2025-07-01'))
            ->setDateFin(new \DateTimeImmutable('2025-07-03'))
            ->setPrixTransport(50)
            ->setPrixEpreuve(30)
            ->setDescription("Concours officiel pour cavaliers de niveau Galop 3 et plus.");

        $manager->persist($competition1);


        $manager->flush();

        //contact
        $contact1 = (new Contact());
        $contact1->setNom("Jehanno")
            ->setPrenom("Damien")
            ->setDescription("cavalier CSO, Jeunes chevaux - pro 1/ International")
            ->setTel("0632935092");
        $manager->persist($contact1);
        $manager->flush();
        $contact2 = (new Contact());
        $contact2->setNom("Kitzing")
            ->setPrenom("Lorraine")
            ->setDescription("Monitrice d'équitation, cavalière dressage, Jeune chevaux-pro 2")
            ->setTel("0698737600");
        $manager->persist($contact2);
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

        $typeSortie3 = (new typeSortie());
        $typeSortie3->setLibelle("Randonnée");
        $manager->persist($typeSortie3);
        $manager->flush();
    }


}