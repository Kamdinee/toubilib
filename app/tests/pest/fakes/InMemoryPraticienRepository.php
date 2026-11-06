<?php
declare(strict_types=1);

namespace tests\pest\fakes;

use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\domain\entities\Praticien;
use toubilib\domain\exceptions\PraticienNotFoundException;

class InMemoryPraticienRepository implements PraticienRepositoryInterface {

    /**
     * @var array<string, Praticien> Stockage des praticiens en memoire
     */
    private array $store = [];

    public function add(Praticien $praticien): void {
        $this->store[$praticien->getId()] = $praticien;
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
}
