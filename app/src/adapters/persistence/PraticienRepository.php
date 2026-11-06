<?php
declare(strict_types=1);

namespace toubilib\adapters\persistence;

use DateTimeImmutable;
use PDO;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\RendezVousStatus;
use toubilib\domain\entities\Specialite;
use toubilib\domain\exceptions\PraticienNotFoundException;

class PraticienRepository implements PraticienRepositoryInterface {

    private const SELECT =
        'SELECT p.id, p.nom, p.prenom, p.ville, p.email, p.telephone, p.titre, p.rpps_id,
                p.nouveau_patient, s.id AS specialite_id, s.libelle AS specialite_libelle,
                s.description AS specialite_description
         FROM praticien p
         JOIN specialite s ON s.id = p.specialite_id';

    public function __construct(
        private PDO $pdo
    ) {}

    public function findAll(): array {
        $stmt = $this->pdo->query(self::SELECT . ' ORDER BY p.nom, p.prenom');

        $praticiens = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $praticiens[] = $this->toEntity($row);
        }
        return $praticiens;
    }

    /**
     * @throws PraticienNotFoundException si aucun praticien ne correspond
     */
    public function findById(string $id): Praticien {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE p.id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new PraticienNotFoundException("Praticien avec l'identifiant $id introuvable");
        }
        return $this->toEntity($row);
    }

    /**
     * @throws PraticienNotFoundException si aucun praticien ne correspond
     */
    public function findByIdAvecRdvsDuJour(string $id, DateTimeImmutable $jour): Praticien {
        $praticien = $this->findById($id);

        $stmt = $this->pdo->prepare(
            'SELECT id, praticien_id, patient_id, date_heure_debut, status, duree, date_heure_fin, date_creation, motif_visite
             FROM rdv
             WHERE praticien_id = :praticien_id
               AND date_heure_debut >= :debut_jour
               AND date_heure_debut < :fin_jour
               AND status <> :annule
             ORDER BY date_heure_debut'
        );
        $stmt->execute([
            'praticien_id' => $id,
            'debut_jour' => $jour->format('Y-m-d 00:00:00'),
            'fin_jour' => $jour->modify('+1 day')->format('Y-m-d 00:00:00'),
            'annule' => RendezVousStatus::ANNULE->value,
        ]);

        $rdvs = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $rdvs[] = new RendezVous(
                id: (string) $row['id'],
                praticienId: (string) $row['praticien_id'],
                patientId: (string) $row['patient_id'],
                dateHeureDebut: new DateTimeImmutable($row['date_heure_debut']),
                duree: (int) $row['duree'],
                dateHeureFin: $row['date_heure_fin'] ? new DateTimeImmutable($row['date_heure_fin']) : null,
                dateCreation: $row['date_creation'] ? new DateTimeImmutable($row['date_creation']) : null,
                motifVisite: $row['motif_visite'],
                status: RendezVousStatus::from((int) $row['status'])
            );
        }
        $praticien->setRdvs($rdvs);

        return $praticien;
    }

    /**
     * Construit l'entite Praticien a partir d'une ligne de la base
     */
    private function toEntity(array $row): Praticien {
        $specialite = new Specialite(
            (int) $row['specialite_id'],
            (string) $row['specialite_libelle'],
            $row['specialite_description'] !== null ? (string) $row['specialite_description'] : null
        );

        return new Praticien(
            id: (string) $row['id'],
            nom: (string) $row['nom'],
            prenom: (string) $row['prenom'],
            ville: (string) $row['ville'],
            email: (string) $row['email'],
            telephone: (string) $row['telephone'],
            specialite: $specialite,
            titre: (string) $row['titre'],
            rppsId: $row['rpps_id'] !== null ? (string) $row['rpps_id'] : null,
            // colonne de type bit(1) : PostgreSQL renvoie '1' ou '0'
            accepteNouveauPatient: (string) $row['nouveau_patient'] === '1'
        );
    }
}
