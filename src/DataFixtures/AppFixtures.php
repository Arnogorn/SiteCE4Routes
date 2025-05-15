<?php

namespace App\DataFixtures;

use App\Entity\Sexe;
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
        $etatLabels = ['Créée', 'Ouverte', 'Clôturée', 'En cours', 'Passée', 'Annulée'];
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
            'Galop 5', 'Galop 6', 'Galop 7',
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
            ->setNiveau($niveaux[10]);

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
        $sortie1 = new \App\Entity\Sortie();
        $sortie1->setTitre("Balade en forêt")
            ->setDate(new \DateTimeImmutable('2025-06-15'))
            ->setDuree(120)
            ->setDateLimiteInscription(new \DateTimeImmutable('2025-06-01'))
            ->setNbInscriptionMax(10)
            ->setPrix(25)
            ->setInfos("Sortie encadrée par un moniteur diplômé. Casque obligatoire.")
            ->setIsPublished(true)
            ->setMoniteur($moniteur1)
            ->setEtat($etats[1]); // Ouvert
        $sortie1->addNiveauxAdmi($niveaux[0]); // Débutant

        $manager->persist($sortie1);

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
    }
}