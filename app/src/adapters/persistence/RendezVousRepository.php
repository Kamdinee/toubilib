<?php
declare(strict_types=1);

namespace toubilib\adapters\persistence;

use DateTimeImmutable;
use PDO;
use PDOException;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\RendezVousStatus;
use toubilib\domain\exceptions\RendezVousNotFoundException;

class RendezVousRepository implements RendezVousRepositoryInterface {

    public function __construct(
        private PDO $pdo
    ) {}

    /**
     * Recherche un rendez-vous par son identifiant
     * 
     * @throws RendezVousNotFoundException si aucun RDV ne correspond
     */
    public function findById(string $id): RendezVous {
        $stmt = $this->pdo->prepare(
            'SELECT id, praticien_id, patient_id, date_heure_debut, status, duree, date_heure_fin, date_creation, motif_visite
             FROM rdv
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new RendezVousNotFoundException("Rendez-vous avec l'identifiant $id introuvable");
        }

        return $this->versEntite($row);
    }

    public function findByPraticienEntre(string $praticienId, DateTimeImmutable $debut, DateTimeImmutable $fin): array {
        $stmt = $this->pdo->prepare(
            'SELECT id, praticien_id, patient_id, date_heure_debut, status, duree, date_heure_fin, date_creation, motif_visite
             FROM rdv
             WHERE praticien_id = :praticien_id
               AND date_heure_debut BETWEEN :debut AND :fin
             ORDER BY date_heure_debut'
        );
        $stmt->execute([
            'praticien_id' => $praticienId,
            'debut' => $debut->format('Y-m-d H:i:s'),
            'fin' => $fin->format('Y-m-d H:i:s'),
        ]);

        return array_map(fn (array $row) => $this->versEntite($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Construit l'entite RendezVous a partir d'une ligne de la table rdv
     */
    private function versEntite(array $row): RendezVous {
        return new RendezVous(
            id: (string) $row['id'],
            praticienId: (string) $row['praticien_id'],
            patientId: (string) $row['patient_id'],
            dateHeureDebut: new DateTimeImmutable($row['date_heure_debut']),
            duree: (int) $row['duree'],
            dateHeureFin: $row['date_heure_fin'] ? new DateTimeImmutable($row['date_heure_fin']) : null,
            dateCreation: $row['date_creation'] ? new DateTimeImmutable($row['date_creation']) : null,
            motifVisite: $row['motif_visite'] !== null ? (string) $row['motif_visite'] : null,
            status: RendezVousStatus::from((int) $row['status'])
        );
    }

    /**
     * Sauvegarde la modification d'un rendez-vous existant
     */
    public function save(RendezVous $rdv): void {
        $stmt = $this->pdo->prepare(
            'UPDATE rdv
             SET status = :status,
                 date_heure_debut = :date_heure_debut,
                 date_heure_fin = :date_heure_fin,
                 duree = :duree,
                 motif_visite = :motif_visite
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $rdv->getId(),
            'status' => $rdv->getStatus()->value,
            'date_heure_debut' => $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'),
            'date_heure_fin' => $rdv->getDateHeureFin()?->format('Y-m-d H:i:s'),
            'duree' => $rdv->getDuree(),
            'motif_visite' => $rdv->getMotifVisite(),
        ]);
    }

    /**
     * Sauvegarde un nouveau rendez-vous dans la base
     */
    public function ajouter(RendezVous $rdv): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO rdv (id, praticien_id, patient_id, date_heure_debut, status, duree, date_heure_fin, date_creation, motif_visite)
             VALUES (:id, :praticien_id, :patient_id, :date_heure_debut, :status, :duree, :date_heure_fin, :date_creation, :motif_visite)'
        );

        $stmt->execute([
            'id' => $rdv->getId(),
            'praticien_id' => $rdv->getPraticienId(),
            'patient_id' => $rdv->getPatientId(),
            'date_heure_debut' => $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'),
            'status' => $rdv->getStatus()->value,
            'duree' => $rdv->getDuree(),
            'date_heure_fin' => $rdv->getDateHeureFin()?->format('Y-m-d H:i:s'),
            'date_creation' => $rdv->getDateCreation()?->format('Y-m-d H:i:s'),
            'motif_visite' => $rdv->getMotifVisite(),
        ]);
    }
}
