<?php
declare(strict_types=1);

namespace tests\pest\fakes;

use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\domain\entities\Patient;
use toubilib\domain\exceptions\PatientNotFoundException;

class InMemoryPatientRepository implements PatientRepositoryInterface {

    /**
     * @var array<string, Patient> Stockage des patients en memoire
     */
    private array $store = [];

    public function add(Patient $patient): void {
        $this->store[$patient->getId()] = $patient;
    }

    public function findById(string $id): Patient {
        if (!isset($this->store[$id])) {
            throw new PatientNotFoundException("Patient avec l'identifiant $id introuvable");
        }
        return $this->store[$id];
    }
}
