<?php
declare(strict_types=1);

namespace toubilib\adapters\persistence;

use PDO;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\domain\entities\Praticien;
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
