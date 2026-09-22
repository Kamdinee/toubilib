<?php
declare(strict_types=1);

namespace toubilib\application\ports\spi;

use toubilib\domain\entities\RendezVous;

interface RendezVousRepositoryInterface {
    /**
     * Recherche un rendez-vous par son identifiant
     * 
     * @throws RendezVousNotFoundException si le RDV n'existe pas
     */
    public function findById(string $id): RendezVous;

    /**
     * Sauvegarde ou met a jour un rendez-vous
     */
    public function save(RendezVous $rdv): void;
}

