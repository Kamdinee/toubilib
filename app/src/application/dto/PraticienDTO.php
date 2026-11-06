<?php
declare(strict_types=1);

namespace toubilib\application\dto;

use toubilib\domain\entities\Praticien;

/**
 * Informations de base d'un praticien, utilisees pour la liste des praticiens
 */
final class PraticienDTO {

    public function __construct(
        public readonly string $id,
        public readonly string $nom,
        public readonly string $prenom,
        public readonly string $ville,
        public readonly string $email,
        public readonly string $specialite
    ) {}

    public static function fromEntity(Praticien $praticien): self {
        return new self(
            id: $praticien->getId(),
            nom: $praticien->getNom(),
            prenom: $praticien->getPrenom(),
            ville: $praticien->getVille(),
            email: $praticien->getEmail(),
            specialite: $praticien->getSpecialite()->getLibelle()
        );
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'ville' => $this->ville,
            'email' => $this->email,
            'specialite' => $this->specialite,
        ];
    }
}
