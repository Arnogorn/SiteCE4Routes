<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Inscription;
use App\Service\Uploader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Politique de rétention RGPD : au-delà d'un certain nombre d'années sans
 * activité (aucune sortie), les données personnelles et de santé d'un membre
 * sont anonymisées. Les enregistrements de paiement (Paiement, Inscription,
 * HistoriquePaiement) sont volontairement CONSERVÉS, l'obligation légale de
 * conservation comptable (10 ans) primant sur la donnée d'identité une fois
 * celle-ci anonymisée : ils restent liés au compte par son id, mais ce
 * compte ne porte plus de données personnelles identifiantes.
 *
 * Par défaut la commande tourne en mode "dry-run" (rapport uniquement).
 * Utiliser --anonymize pour appliquer réellement les changements.
 */
#[AsCommand(
    name: 'app:rgpd:purge-inactive-members',
    description: 'Liste, puis anonymise sur demande, les membres inactifs depuis plus de N années (politique de rétention RGPD)',
)]
class RgpdPurgeInactiveMembersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private Uploader $uploader,
        private string $participantPictureDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('years', null, InputOption::VALUE_REQUIRED, 'Ancienneté d\'inactivité (en années) au-delà de laquelle un compte est éligible', '3')
            ->addOption('anonymize', null, InputOption::VALUE_NONE, 'Applique réellement l\'anonymisation (sans cette option : simple rapport)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $years = (int) $input->getOption('years');
        $apply = (bool) $input->getOption('anonymize');
        $seuil = (new \DateTimeImmutable())->modify("-{$years} years");

        // Pour chaque utilisateur, on récupère la date de sortie la plus récente
        // parmi ses inscriptions (en tant que participant ou en tant que personne
        // ayant inscrit un membre de sa famille).
        $rows = $this->em->createQueryBuilder()
            ->select('u AS utilisateur', 'MAX(s.date) AS derniereActivite')
            ->from(User::class, 'u')
            ->leftJoin(Inscription::class, 'i', Join::WITH, 'i.utilisateur = u OR i.inscritPar = u')
            ->leftJoin('i.sortie', 's')
            ->groupBy('u.id')
            ->getQuery()
            ->getResult();

        $eligibles = [];
        $jamaisActifs = [];

        foreach ($rows as $row) {
            /** @var User $user */
            $user = $row['utilisateur'];

            // On ne touche jamais aux comptes administrateurs
            if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                continue;
            }

            // Compte déjà anonymisé par un run précédent
            if (str_starts_with($user->getEmail(), 'anonymise-')) {
                continue;
            }

            if ($row['derniereActivite'] === null) {
                // Jamais participé à une sortie : on ne peut pas dater l'ancienneté
                // du compte (pas de champ createdAt sur User), donc on ne l'inclut
                // pas dans le traitement automatique et on le signale pour revue manuelle.
                $jamaisActifs[] = $user;
                continue;
            }

            $derniereActivite = new \DateTimeImmutable($row['derniereActivite']);
            if ($derniereActivite < $seuil) {
                $eligibles[$user->getId()] = ['user' => $user, 'derniereActivite' => $derniereActivite];
            }
        }

        if (empty($eligibles) && empty($jamaisActifs)) {
            $io->success('Aucun compte à traiter.');
            return Command::SUCCESS;
        }

        if (!empty($eligibles)) {
            $io->section(sprintf('Comptes inactifs depuis plus de %d an(s)', $years));
            $io->table(
                ['ID', 'Nom', 'Email', 'Dernière activité'],
                array_map(fn($e) => [
                    $e['user']->getId(),
                    $e['user']->getPrenom() . ' ' . $e['user']->getNom(),
                    $e['user']->getEmail(),
                    $e['derniereActivite']->format('d/m/Y'),
                ], $eligibles)
            );
        }

        if (!empty($jamaisActifs)) {
            $io->section('Comptes sans aucune activité enregistrée (à vérifier manuellement)');
            $io->table(
                ['ID', 'Nom', 'Email'],
                array_map(fn(User $u) => [$u->getId(), $u->getPrenom() . ' ' . $u->getNom(), $u->getEmail()], $jamaisActifs)
            );
        }

        if (!$apply) {
            $io->note('Mode rapport uniquement. Relancez avec --anonymize pour appliquer réellement l\'anonymisation aux comptes listés ci-dessus (hors "sans activité enregistrée", à traiter au cas par cas).');
            return Command::SUCCESS;
        }

        if (empty($eligibles)) {
            $io->success('Rien à anonymiser.');
            return Command::SUCCESS;
        }

        if (!$io->confirm(sprintf('Anonymiser définitivement %d compte(s) et les membres de famille associés ?', count($eligibles)), false)) {
            $io->warning('Annulé.');
            return Command::SUCCESS;
        }

        foreach ($eligibles as $e) {
            $this->anonymiser($e['user']);
        }
        $this->em->flush();

        $io->success(sprintf('%d compte(s) anonymisé(s).', count($eligibles)));

        return Command::SUCCESS;
    }

    private function anonymiser(User $user): void
    {
        if ($user->getPhoto()) {
            $this->uploader->delete($user->getPhoto(), $this->participantPictureDir);
        }

        $user->setNom('Anonymisé');
        $user->setPrenom('Anonymisé');
        $user->setEmail(sprintf('anonymise-%d@ecuriesdes4routes.fr', $user->getId()));
        $user->setAdresse('Anonymisé');
        $user->setTelephone('0000000000');
        $user->setTelPersContact(null);
        $user->setAllergies(null);
        $user->setMedecinTraitant(null);
        $user->setTelMedecin(null);
        $user->setPhoto(null);
        $user->setInfos(null);
        $user->setConsentementDonneesSante(false);
        $user->setActif(false);

        foreach ($user->getFamille()?->getMembre() ?? [] as $membre) {
            if ($membre->getNoLicence()) {
                $membre->setNoLicence(null);
            }
            $membre->setNom('Anonymisé');
            $membre->setPrenom('Anonymisé');
            $membre->setAllergies(null);
            $membre->setMedecinTraitant(null);
            $membre->setTelMedecin(null);
            $membre->setConsentementDonneesSante(false);
        }

        $this->em->persist($user);
    }
}
