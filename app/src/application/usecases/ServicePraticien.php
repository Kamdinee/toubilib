<?php
declare(strict_types=1);

namespace toubilib\application\usecases;

use toubilib\application\dto\PraticienDetailDTO;
use toubilib\application\dto\PraticienDTO;
use toubilib\application\ports\api\ServicePraticienInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;

class ServicePraticien implements ServicePraticienInterface {

    public function __construct(
        private PraticienRepositoryInterface $praticienRepository
    ) {}

    public function listerPraticiens(): array {
        return array_map(
            fn ($praticien) => PraticienDTO::fromEntity($praticien),
            $this->praticienRepository->findAll()
        );
    }

    public function afficherPraticien(string $id): PraticienDetailDTO {
        return PraticienDetailDTO::fromEntity($this->praticienRepository->findById($id));
    }
}
