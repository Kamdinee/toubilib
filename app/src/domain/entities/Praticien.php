<?php
declare(strict_types=1);

namespace toubilib\domain\entities;

class Praticien {

    public function __construct(
        private string $id,
        private string $nom,
        private string $prenom,
        private string $ville,
        private string $email,
        private string $telephone,
        private Specialite $specialite,
        private string $titre = 'Dr.',
        private ?string $rppsId = null,
        private bool $accepteNouveauPatient = true
    ) {}

    public function getId(): string {
        return $this->id;
    }

    public function getNom(): string {
        return $this->nom;
    }

    public function getPrenom(): string {
        return $this->prenom;
    }

    public function getVille(): string {
        return $this->ville;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getTelephone(): string {
        return $this->telephone;
    }

    public function getSpecialite(): Specialite {
        return $this->specialite;
    }

    public function getTitre(): string {
        return $this->titre;
    }

    public function getRppsId(): ?string {
        return $this->rppsId;
    }

    public function accepteNouveauPatient(): bool {
        return $this->accepteNouveauPatient;
    }
}
