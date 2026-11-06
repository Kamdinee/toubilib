<?php
declare(strict_types=1);

namespace toubilib\domain\exceptions;

/**
 * Le RDV demande chevauche un RDV existant du praticien
 */
class PraticienIndisponibleException extends \DomainException {
}
