<?php
declare(strict_types=1);

namespace toubilib\application\ports\spi;

use toubilib\domain\entities\Praticien;
use toubilib\domain\exceptions\PraticienNotFoundException;

interface PraticienRepositoryInterface {
    /**
     * Retourne tous les praticiens
     *
     * @return Praticien[]
     */
    public function findAll(): array;

    /**
     * Recherche un praticien par son identifiant
     *
     * @throws PraticienNotFoundException si le praticien n'existe pas
     */
    public function findById(string $id): Praticien;
}
