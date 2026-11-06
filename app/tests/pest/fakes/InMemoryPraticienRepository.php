<?php
declare(strict_types=1);

namespace tests\pest\fakes;

use DateTimeImmutable;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\RendezVousStatus;
use toubilib\domain\exceptions\PraticienNotFoundException;

class InMemoryPraticienRepository implements PraticienRepositoryInterface {

    /**
     * @var array<string, Praticien> Stockage des praticiens en memoire
     */
    private array $store = [];

    /**
     * @var RendezVous[] RDV connus des praticiens
     */
    private array $rdvs = [];

    public function add(Praticien $praticien): void {
        $this->store[$praticien->getId()] = $praticien;
    }

    public function addRdv(RendezVous $rdv): void {
        $this->rdvs[] = $rdv;
    }

    public function findAll(): array {
        return array_values($this->store);
    }

    public function findById(string $id): Praticien {
        if (!isset($this->store[$id])) {
            throw new PraticienNotFoundException("Praticien avec l'identifiant $id introuvable");
        }
        return $this->store[$id];
    }

    public function findByIdAvecRdvsDuJour(string $id, DateTimeImmutable $jour): Praticien {
        $praticien = $this->findById($id);
        $praticien->setRdvs(array_values(array_filter(
            $this->rdvs,
            fn (RendezVous $rdv) => $rdv->getPraticienId() === $id
                && $rdv->getDateHeureDebut()->format('Y-m-d') === $jour->format('Y-m-d')
                && $rdv->getStatus() !== RendezVousStatus::ANNULE
        )));
        return $praticien;
    }
}
