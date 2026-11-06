<?php
declare(strict_types=1);

namespace toubilib\domain\entities;

/**
 * Statut d'un rendez-vous, tel que stocke dans la colonne rdv.status.
 * Les donnees de depart utilisent -1 pour les RDV annules.
 */
enum RendezVousStatus: int {
    case PREVU = 0;
    case ANNULE = -1;
    case HONORE = 2;
    case IGNORE = 3;

    public static function fromInt(int $status): ?self {
        return self::tryFrom($status);
    }
}

