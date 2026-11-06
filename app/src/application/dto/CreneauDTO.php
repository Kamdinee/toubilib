<?php
declare(strict_types=1);

namespace toubilib\application\dto;

use toubilib\domain\entities\RendezVous;

/**
 * Creneau occupe dans l'agenda d'un praticien
 */
final class CreneauDTO {

    public function __construct(
        public readonly string $dateHeureDebut,
        public readonly string $dateHeureFin
    ) {}

    public static function fromEntity(RendezVous $rdv): self {
        return new self(
            dateHeureDebut: $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'),
            dateHeureFin: $rdv->getDateHeureFin()->format('Y-m-d H:i:s')
        );
    }
}
