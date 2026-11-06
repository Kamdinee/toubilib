<?php
declare(strict_types=1);

namespace toubilib\application\exceptions;

/**
 * Les donnees recues sont incompletes ou mal formees
 */
class DonneesInvalidesException extends \InvalidArgumentException {

    /**
     * @param array<string, string> $erreurs message d'erreur pour chaque champ invalide
     */
    public function __construct(private array $erreurs) {
        parent::__construct('Donnees invalides : ' . implode(' ; ', $erreurs));
    }

    /**
     * @return array<string, string>
     */
    public function getErreurs(): array {
        return $this->erreurs;
    }
}
