<?php
declare(strict_types=1);

namespace toubilib\application\usecases;

use toubilib\application\dto\RendezVousDTO;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;

class ServiceRendezVous implements ServiceRendezVousInterface {

    public function __construct(
        private RendezVousRepositoryInterface $rendezVousRepository
    ) {}

    /**
     * Retourne le detail d'un rendez-vous
     *
     * @throws RendezVousNotFoundException si le RDV n'existe pas
     */
    public function consulter(string $id): RendezVousDTO {
        return RendezVousDTO::fromEntity($this->rendezVousRepository->findById($id));
    }

    /**
     * Annule un rendez-vous et sauvegarde sa modification d'etat
     * 
     * @throws RendezVousNotFoundException si le RDV n'existe pas
     * @throws StatusChangeNotAllowedException si la regle metier refuse l'annulation
     */
    public function annuler(string $id): RendezVousDTO {
        // 1 recuperer l'entite depuis la persistance
        $rdv = $this->rendezVousRepository->findById($id);

        // 2 appliquer la regle metier du domaine
        $rdv->annuler();

        // 3 sauvegarder la modification d'etat
        $this->rendezVousRepository->save($rdv);

        // 4 retourner les informations sous forme de DTO
        return RendezVousDTO::fromEntity($rdv);
    }
}

