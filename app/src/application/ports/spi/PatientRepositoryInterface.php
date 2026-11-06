<?php
declare(strict_types=1);

namespace toubilib\application\ports\spi;

use toubilib\domain\entities\Patient;
use toubilib\domain\exceptions\PatientNotFoundException;

interface PatientRepositoryInterface {
    /**
     * Recherche un patient par son identifiant
     *
     * @throws PatientNotFoundException si le patient n'existe pas
     */
    public function findById(string $id): Patient;
}
