<?php
declare(strict_types=1);

namespace toubilib\application\validators;

use DateTimeImmutable;
use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\domain\entities\Praticien;
use toubilib\domain\exceptions\PatientNotFoundException;
use toubilib\domain\exceptions\PraticienNotFoundException;
use toubilib\domain\exceptions\RendezVousInvalideException;

/**
 * Validation metier des donnees de creation d'un rendez-vous :
 * - le praticien et le patient indiques existent,
 * - le motif est accepte par le praticien,
 * - la date et l'heure sont acceptables pour le praticien.
 *
 * La disponibilite du praticien est verifiee par le domaine lors de la
 * creation du RDV (RendezVous::creer), avec l'agenda du jour du praticien.
 */
class RdvValidator {

    public const FORMAT_DATE = 'Y-m-d H:i';

    public function __construct(
        private PraticienRepositoryInterface $praticienRepository,
        private PatientRepositoryInterface $patientRepository
    ) {}

    /**
     * @throws RendezVousInvalideException si les donnees ne sont pas valides
     */
    public function valider(CreateRdvDTO $dto, ?DateTimeImmutable $now = null): void {
        $now = $now ?? new DateTimeImmutable();

        // le praticien indique existe
        try {
            $praticien = $this->praticienRepository->findById($dto->praticienId);
        } catch (PraticienNotFoundException $e) {
            throw new RendezVousInvalideException("Le praticien {$dto->praticienId} n'existe pas");
        }

        // le patient indique existe
        try {
            $this->patientRepository->findById($dto->patientId);
        } catch (PatientNotFoundException $e) {
            throw new RendezVousInvalideException("Le patient {$dto->patientId} n'existe pas");
        }

        // le motif est correct pour le praticien
        if (!$praticien->accepteMotif($dto->motifVisite)) {
            throw new RendezVousInvalideException(
                "Le motif '{$dto->motifVisite}' n'est pas accepte, motifs possibles : "
                . implode(', ', array_keys(Praticien::MOTIFS))
            );
        }

        // la date et l'heure sont acceptables pour le praticien
        $debut = self::lireDate($dto->dateHeureDebut);
        if ($debut <= $now) {
            throw new RendezVousInvalideException("La date du rendez-vous doit etre dans le futur");
        }
        if (!$praticien->accepteDate($debut, $praticien->dureeRdv($dto->motifVisite))) {
            throw new RendezVousInvalideException(
                "Le praticien ne prend pas de rendez-vous a cette date : du lundi au vendredi, entre "
                . Praticien::HEURE_DEBUT . " et " . Praticien::HEURE_FIN
            );
        }
    }

    /**
     * Convertit la date recue (format Y-m-d H:i) en DateTimeImmutable
     *
     * @throws RendezVousInvalideException si la date n'est pas au bon format
     */
    public static function lireDate(string $date): DateTimeImmutable {
        $debut = DateTimeImmutable::createFromFormat(self::FORMAT_DATE, $date);
        if ($debut === false || $debut->format(self::FORMAT_DATE) !== $date) {
            throw new RendezVousInvalideException("La date '$date' n'est pas au format AAAA-MM-JJ HH:MM");
        }
        return $debut;
    }
}
