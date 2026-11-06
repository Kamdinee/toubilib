<?php
declare(strict_types=1);

namespace toubilib\domain\entities;

use DateTimeInterface;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use toubilib\domain\exceptions\PraticienIndisponibleException;
use toubilib\domain\exceptions\RendezVousInvalideException;
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
     * Validation-creation d'un rendez-vous : verifie le motif, la date et la
     * disponibilite du praticien, puis cree le rendez-vous.
     * Le praticien doit avoir ete charge avec ses RDV de la journee demandee.
     *
     * @throws RendezVousInvalideException si le motif ou la date ne sont pas valides
     * @throws PraticienIndisponibleException si le RDV chevauche un RDV du praticien
     */
    public static function creer(
        Praticien $praticien,
        Patient $patient,
        DateTimeImmutable $dateHeureDebut,
        string $motif,
        ?DateTimeImmutable $now = null
    ): self {
        $now = $now ?? new DateTimeImmutable();

        // le motif doit etre accepte par le praticien, il determine la duree
        $duree = $praticien->dureeRdv($motif);

        // la date doit etre dans le futur
        if ($dateHeureDebut <= $now) {
            throw new RendezVousInvalideException("La date du rendez-vous doit etre dans le futur");
        }

        // la date doit etre possible pour le praticien
        if (!$praticien->accepteDate($dateHeureDebut, $duree)) {
            throw new RendezVousInvalideException(
                "Le praticien ne prend pas de rendez-vous a cette date : du lundi au vendredi, entre "
                . Praticien::HEURE_DEBUT . " et " . Praticien::HEURE_FIN
            );
        }

        $rdv = new self(
            id: Uuid::uuid4()->toString(),
            praticienId: $praticien->getId(),
            patientId: $patient->getId(),
            dateHeureDebut: $dateHeureDebut,
            duree: $duree,
            dateCreation: $now,
            motifVisite: $motif
        );

        // le praticien doit etre disponible : aucun de ses RDV ne doit chevaucher le nouveau
        $praticien->verifierDisponibilite($rdv);

        return $rdv;
    }

    /**
     * Indique si ce rendez-vous chevauche un autre rendez-vous.
     *
     * Deux RDV [debutA, finA[ et [debutB, finB[ se chevauchent si et seulement si
     *     debutA < finB  ET  debutB < finA
     * Un RDV qui commence exactement a la fin d'un autre ne le chevauche donc pas.
     * Un RDV annule ne bloque plus son creneau : il ne chevauche rien.
     */
    public function chevauche(RendezVous $autre): bool {
        if ($this->status === RendezVousStatus::ANNULE || $autre->status === RendezVousStatus::ANNULE) {
            return false;
        }

        return $this->dateHeureDebut < $autre->dateHeureFin
            && $autre->dateHeureDebut < $this->dateHeureFin;
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

