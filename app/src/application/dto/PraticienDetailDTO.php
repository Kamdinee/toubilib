<?php
declare(strict_types=1);

namespace toubilib\application\dto;

use toubilib\domain\entities\Praticien;

/**
 * Detail d'un praticien : titre, nom, prenom, specialite, contacts et adresse
 */
final class PraticienDetailDTO {

    public function __construct(
        public readonly string $id,
        public readonly string $titre,
        public readonly string $nom,
        public readonly string $prenom,
        public readonly string $specialite,
        public readonly string $email,
        public readonly string $telephone,
        public readonly string $ville,
        public readonly ?string $rppsId,
        public readonly bool $accepteNouveauPatient
    ) {}

    public static function fromEntity(Praticien $praticien): self {
        return new self(
            id: $praticien->getId(),
            titre: $praticien->getTitre(),
            nom: $praticien->getNom(),
            prenom: $praticien->getPrenom(),
            specialite: $praticien->getSpecialite()->getLibelle(),
            email: $praticien->getEmail(),
            telephone: $praticien->getTelephone(),
            ville: $praticien->getVille(),
            rppsId: $praticien->getRppsId(),
            accepteNouveauPatient: $praticien->accepteNouveauPatient()
        );
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'specialite' => $this->specialite,
            'contacts' => [
                'email' => $this->email,
                'telephone' => $this->telephone,
            ],
            'adresse' => [
                'ville' => $this->ville,
            ],
            'rpps_id' => $this->rppsId,
            'accepte_nouveau_patient' => $this->accepteNouveauPatient,
        ];
    }
}
