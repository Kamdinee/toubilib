<?php
declare(strict_types=1);

namespace toubilib\domain\entities;

use DateTimeInterface;
use DateTimeImmutable;
use toubilib\domain\exceptions\StatusChangeNotAllowedException;

class RendezVous {

    public function __construct(
        private string $id,
        private string $praticienId,
        private string $patientId,
        private DateTimeInterface $dateHeureDebut,
        private int $duree = 30,
        private ?DateTimeInterface $dateHeureFin = null,
        private ?DateTimeInterface $dateCreation = null,
        private ?string $motifVisite = null,
        private RendezVousStatus $status = RendezVousStatus::PREVU
    ) {
        if ($this->dateHeureFin === null) {
            // calcule la date de fin = début + durée en minutes
            $this->dateHeureFin = (new DateTimeImmutable($this->dateHeureDebut->format('Y-m-d H:i:s')))
                ->modify("+{$this->duree} minutes");
        }
        if ($this->dateCreation === null) {
            $this->dateCreation = new DateTimeImmutable();
        }
    }

    /**
     * annule le rendez-vous si les conditions métier sont respectées
     * 
     * @throws StatusChangeNotAllowedException si le RDV est déjà annulé/honoré/ignoré ou passé.
     */
    public function annuler(?DateTimeInterface $now = null): void {
        $now = $now ?? new DateTimeImmutable();

        if ($this->status !== RendezVousStatus::PREVU) {
            throw new StatusChangeNotAllowedException(
                "Impossible d'annuler un rendez-vous qui n'est pas à l'état PREVU (statut actuel : {$this->status->name})."
            );
        }

        if ($this->dateHeureDebut < $now) {
            throw new StatusChangeNotAllowedException(
                "Impossible d'annuler un rendez-vous dont la date est déjà passée."
            );
        }

        $this->status = RendezVousStatus::ANNULE;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getPraticienId(): string {
        return $this->praticienId;
    }

    public function getPatientId(): string {
        return $this->patientId;
    }

    public function getDateHeureDebut(): DateTimeInterface {
        return $this->dateHeureDebut;
    }

    public function getDateHeureFin(): ?DateTimeInterface {
        return $this->dateHeureFin;
    }

    public function getDateCreation(): ?DateTimeInterface {
        return $this->dateCreation;
    }

    public function getDuree(): int {
        return $this->duree;
    }

    public function getMotifVisite(): ?string {
        return $this->motifVisite;
    }

    public function getStatus(): RendezVousStatus {
        return $this->status;
    }
}

