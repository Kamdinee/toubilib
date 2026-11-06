<?php
declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\dto\RendezVousDTO;

interface ServiceRendezVousInterface {
    /**
     * Retourne le detail d'un rendez-vous
     *
     * @throws RendezVousNotFoundException si le RDV n'existe pas
     */
    public function consulter(string $id): RendezVousDTO;

    /**
     * Cree un rendez-vous a partir des donnees recues
     *
     * @throws RendezVousInvalideException si les donnees ne sont pas valides
     * @throws PraticienIndisponibleException si le praticien n'est pas disponible
     */
    public function creerRendezVous(CreateRdvDTO $dto): RendezVousDTO;

    /**
     * Annule un rendez-vous a partir de son identifiant
     * 
     * @throws RendezVousNotFoundException si le RDV n'existe pas
     * @throws StatusChangeNotAllowedException si le RDV ne peut pas etre annule
     */
    public function annuler(string $id): RendezVousDTO;
}

