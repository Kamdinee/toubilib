<?php
declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\PraticienDetailDTO;
use toubilib\application\dto\PraticienDTO;
use toubilib\domain\exceptions\PraticienNotFoundException;

interface ServicePraticienInterface {
    /**
     * Liste tous les praticiens
     *
     * @return PraticienDTO[]
     */
    public function listerPraticiens(): array;

    /**
     * Retourne le detail d'un praticien
     *
     * @throws PraticienNotFoundException si le praticien n'existe pas
     */
    public function afficherPraticien(string $id): PraticienDetailDTO;
}
