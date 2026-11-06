<?php
declare(strict_types=1);

namespace toubilib\application\usecases;

use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\application\validators\RdvValidator;
use toubilib\domain\entities\RendezVous;

class ServiceRendezVous implements ServiceRendezVousInterface {

    public function __construct(
        private RendezVousRepositoryInterface $rendezVousRepository,
        private PraticienRepositoryInterface $praticienRepository,
        private PatientRepositoryInterface $patientRepository,
        private RdvValidator $rdvValidator
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
     * Cree un rendez-vous et le fait persister
     *
     * @throws RendezVousInvalideException si les donnees ne sont pas valides
     * @throws PraticienIndisponibleException si le praticien n'est pas disponible
     */
    public function creerRendezVous(CreateRdvDTO $dto): RendezVousDTO {
        // 1 validation metier des donnees recues
        $this->rdvValidator->valider($dto);
        $debut = RdvValidator::lireDate($dto->dateHeureDebut);

        // 2 chargement du patient et du praticien avec son agenda de la journee
        $patient = $this->patientRepository->findById($dto->patientId);
        $praticien = $this->praticienRepository->findByIdAvecRdvsDuJour($dto->praticienId, $debut);

        // 3 creation de l'entite : verifie motif, date et disponibilite du praticien
        $rdv = RendezVous::creer($praticien, $patient, $debut, $dto->motifVisite);

        // 4 persistance du nouveau rendez-vous
        $this->rendezVousRepository->ajouter($rdv);

        return RendezVousDTO::fromEntity($rdv);
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

    /**
     * Marque un rendez-vous comme honore et sauvegarde sa modification d'etat
     */
    public function honorer(string $id): RendezVousDTO {
        $rdv = $this->rendezVousRepository->findById($id);
        $rdv->honorer();
        $this->rendezVousRepository->save($rdv);

        return RendezVousDTO::fromEntity($rdv);
    }

    /**
     * Marque un rendez-vous comme ignore et sauvegarde sa modification d'etat
     */
    public function ignorer(string $id): RendezVousDTO {
        $rdv = $this->rendezVousRepository->findById($id);
        $rdv->ignorer();
        $this->rendezVousRepository->save($rdv);

        return RendezVousDTO::fromEntity($rdv);
    }
}
