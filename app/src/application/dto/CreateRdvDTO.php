<?php
declare(strict_types=1);

namespace toubilib\application\dto;

/**
 * Donnees de creation d'un nouveau rendez-vous
 */
final class CreateRdvDTO {

    public function __construct(
        public readonly string $praticienId,
        public readonly string $patientId,
        public readonly string $dateHeureDebut,
        public readonly string $motifVisite
    ) {}

    public function toArray(): array {
        return [
            'praticien_id' => $this->praticienId,
            'patient_id' => $this->patientId,
            'date_heure_debut' => $this->dateHeureDebut,
            'motif_visite' => $this->motifVisite,
        ];
    }
}
