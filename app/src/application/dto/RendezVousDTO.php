<?php
declare(strict_types=1);

namespace toubilib\application\dto;

use toubilib\domain\entities\RendezVous;

final class RendezVousDTO {

    public function __construct(
        public readonly string $id,
        public readonly string $praticienId,
        public readonly string $patientId,
        public readonly string $dateHeureDebut,
        public readonly ?string $dateHeureFin,
        public readonly ?string $dateCreation,
        public readonly int $duree,
        public readonly ?string $motifVisite,
        public readonly int $status,
        public readonly string $statusLibelle
    ) {}

    /**
     * Crée un DTO à partir de l'entité métier RendezVous
     */
    public static function fromEntity(RendezVous $rdv): self {
        return new self(
            id: $rdv->getId(),
            praticienId: $rdv->getPraticienId(),
            patientId: $rdv->getPatientId(),
            dateHeureDebut: $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'),
            dateHeureFin: $rdv->getDateHeureFin()?->format('Y-m-d H:i:s'),
            dateCreation: $rdv->getDateCreation()?->format('Y-m-d H:i:s'),
            duree: $rdv->getDuree(),
            motifVisite: $rdv->getMotifVisite(),
            status: $rdv->getStatus()->value,
            statusLibelle: $rdv->getStatus()->name
        );
    }

    /**
     * Convertit le DTO en tableau associatif (pratique pour Slim / json_encode)
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'praticien_id' => $this->praticienId,
            'patient_id' => $this->patientId,
            'date_heure_debut' => $this->dateHeureDebut,
            'date_heure_fin' => $this->dateHeureFin,
            'date_creation' => $this->dateCreation,
            'duree' => $this->duree,
            'motif_visite' => $this->motifVisite,
            'status' => $this->status,
            'status_libelle' => $this->statusLibelle,
        ];
    }
}

