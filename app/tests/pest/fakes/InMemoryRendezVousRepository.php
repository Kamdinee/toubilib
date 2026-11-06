<?php
declare(strict_types=1);

namespace tests\pest\fakes;

use DateTimeImmutable;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\exceptions\RendezVousNotFoundException;

class InMemoryRendezVousRepository implements RendezVousRepositoryInterface {

    /**
     * @var array<string, RendezVous> Stockage des rendez-vous en memoire
     */
    private array $store = [];

    public function save(RendezVous $rdv): void {
        $this->store[$rdv->getId()] = $rdv;
    }

    public function ajouter(RendezVous $rdv): void {
        $this->store[$rdv->getId()] = $rdv;
    }

    public function findById(string $id): RendezVous {
        if (!isset($this->store[$id])) {
            throw new RendezVousNotFoundException("Rendez-vous avec l'identifiant $id introuvable");
        }
        return $this->store[$id];
    }

    public function findByPraticienEntre(string $praticienId, DateTimeImmutable $debut, DateTimeImmutable $fin): array {
        $rdvs = array_filter(
            $this->store,
            fn (RendezVous $rdv) => $rdv->getPraticienId() === $praticienId
                && $rdv->getDateHeureDebut() >= $debut
                && $rdv->getDateHeureDebut() <= $fin
        );
        usort($rdvs, fn (RendezVous $a, RendezVous $b) => $a->getDateHeureDebut() <=> $b->getDateHeureDebut());
        return $rdvs;
    }
}
