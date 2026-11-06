<?php
declare(strict_types=1);

namespace toubilib\adapters\persistence;

use DateTimeImmutable;
use PDO;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\domain\entities\Patient;
use toubilib\domain\exceptions\PatientNotFoundException;

class PatientRepository implements PatientRepositoryInterface {

    public function __construct(
        private PDO $pdo
    ) {}

    /**
     * @throws PatientNotFoundException si aucun patient ne correspond
     */
    public function findById(string $id): Patient {
        $stmt = $this->pdo->prepare(
            'SELECT id, nom, prenom, date_naissance, adresse, code_postal, ville, email, telephone
             FROM patient
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new PatientNotFoundException("Patient avec l'identifiant $id introuvable");
        }

        return new Patient(
            id: (string) $row['id'],
            nom: (string) $row['nom'],
            prenom: (string) $row['prenom'],
            telephone: (string) $row['telephone'],
            dateNaissance: $row['date_naissance'] !== null ? new DateTimeImmutable($row['date_naissance']) : null,
            adresse: $row['adresse'],
            codePostal: $row['code_postal'],
            ville: $row['ville'],
            email: $row['email']
        );
    }
}
