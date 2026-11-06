<?php
declare(strict_types=1);

namespace toubilib\application\ports\spi;

use DateTimeImmutable;
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

    /**
     * Charge un praticien avec ses rendez-vous non annules de la journee donnee
     * (sous-ensemble suffisant pour verifier sa disponibilite ce jour-la)
     *
     * @throws PraticienNotFoundException si le praticien n'existe pas
     */
    public function findByIdAvecRdvsDuJour(string $id, DateTimeImmutable $jour): Praticien;
}
