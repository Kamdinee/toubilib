<?php
declare(strict_types=1);

namespace toubilib\application\ports\spi;

use DateTimeImmutable;
use toubilib\domain\entities\RendezVous;

interface RendezVousRepositoryInterface {
    /**
     * Recherche un rendez-vous par son identifiant
     * 
     * @throws RendezVousNotFoundException si le RDV n'existe pas
     */
    public function findById(string $id): RendezVous;

    /**
     * Sauvegarde la modification d'un rendez-vous existant
     */
    public function save(RendezVous $rdv): void;

    /**
     * Sauvegarde un nouveau rendez-vous
     */
    public function ajouter(RendezVous $rdv): void;

    /**
     * Rendez-vous d'un praticien commencant entre le debut et la fin donnes (inclus),
     * tries par date de debut
     *
     * @return RendezVous[]
     */
    public function findByPraticienEntre(string $praticienId, DateTimeImmutable $debut, DateTimeImmutable $fin): array;
}
