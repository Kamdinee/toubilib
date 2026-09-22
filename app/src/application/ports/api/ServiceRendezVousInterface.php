<?php
declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\RendezVousDTO;

interface ServiceRendezVousInterface {
    /**
     * Annule un rendez-vous a partir de son identifiant
     * 
     * @throws RendezVousNotFoundException si le RDV n'existe pas
     * @throws StatusChangeNotAllowedException si le RDV ne peut pas etre annule
     */
    public function annuler(string $id): RendezVousDTO;
}

