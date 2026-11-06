<?php
declare(strict_types=1);

namespace toubilib\application\validators;

use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\domain\exceptions\PatientNotFoundException;
use toubilib\domain\exceptions\PraticienNotFoundException;
use toubilib\domain\exceptions\RendezVousInvalideException;

/**
 * Validation metier des donnees de creation d'un rendez-vous
 */
class RdvValidator {

    public function __construct(
        private PraticienRepositoryInterface $praticienRepository,
        private PatientRepositoryInterface $patientRepository
    ) {}

    /**
     * @throws RendezVousInvalideException si les donnees ne sont pas valides
     */
    public function valider(CreateRdvDTO $dto): void {
        // le praticien indique existe
        try {
            $this->praticienRepository->findById($dto->praticienId);
        } catch (PraticienNotFoundException $e) {
            throw new RendezVousInvalideException("Le praticien {$dto->praticienId} n'existe pas");
        }

        // le patient indique existe
        try {
            $this->patientRepository->findById($dto->patientId);
        } catch (PatientNotFoundException $e) {
            throw new RendezVousInvalideException("Le patient {$dto->patientId} n'existe pas");
        }
    }
}
