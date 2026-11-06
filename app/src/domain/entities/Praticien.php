<?php
declare(strict_types=1);

namespace toubilib\domain\entities;

use DateTimeImmutable;
use toubilib\domain\exceptions\PraticienIndisponibleException;
use toubilib\domain\exceptions\RendezVousInvalideException;

class Praticien {

    /**
     * Motifs de visite acceptes et duree du RDV associee (en minutes)
     * Simplification du projet : tous les praticiens utilisent les memes motifs
     */
    public const MOTIFS = [
        'CI' => 30, // consultation initiale
        'CO' => 20, // consultation
        'CS' => 15, // consultation de suivi
    ];

    /** Plage horaire des RDV acceptes */
    public const HEURE_DEBUT = '09:00';
    public const HEURE_FIN = '19:00';

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
        private bool $accepteNouveauPatient = true,
        private array $rdvs = []
    ) {}

    /**
     * Verifie que le motif de RDV demande est accepte par le praticien
     */
    public function accepteMotif(string $motif): bool {
        return array_key_exists($motif, self::MOTIFS);
    }

    /**
     * Calcule la duree d'un RDV (en minutes) en fonction du motif
     *
     * @throws RendezVousInvalideException si le motif n'est pas accepte
     */
    public function dureeRdv(string $motif): int {
        if (!$this->accepteMotif($motif)) {
            throw new RendezVousInvalideException("Le motif de visite '$motif' n'est pas accepte par ce praticien");
        }
        return self::MOTIFS[$motif];
    }

    /**
     * Verifie que la date et l'heure demandees sont possibles pour le praticien :
     * jour ouvrable (lundi au vendredi) et RDV entierement compris dans la plage horaire
     */
    public function accepteDate(DateTimeImmutable $debut, int $duree): bool {
        // format 'N' : 1 (lundi) a 7 (dimanche)
        if ((int) $debut->format('N') > 5) {
            return false;
        }

        $fin = $debut->modify("+$duree minutes");
        $ouverture = $debut->modify(self::HEURE_DEBUT);
        $fermeture = $debut->modify(self::HEURE_FIN);

        return $debut >= $ouverture && $fin <= $fermeture;
    }

    /**
     * RDV connus du praticien, remplis par le repository au chargement du praticien.
     * Pour verifier une disponibilite, les RDV de la journee concernee suffisent.
     *
     * @param RendezVous[] $rdvs
     */
    public function setRdvs(array $rdvs): void {
        $this->rdvs = $rdvs;
    }

    /**
     * @return RendezVous[]
     */
    public function getRdvs(): array {
        return $this->rdvs;
    }

    /**
     * Verifie que le RDV demande ne chevauche aucun RDV du praticien
     *
     * @throws PraticienIndisponibleException si le praticien n'est pas disponible
     */
    public function verifierDisponibilite(RendezVous $rdvDemande): void {
        foreach ($this->rdvs as $rdv) {
            if ($rdvDemande->chevauche($rdv)) {
                throw new PraticienIndisponibleException(
                    "Le praticien n'est pas disponible : un rendez-vous existe deja de "
                    . $rdv->getDateHeureDebut()->format('H:i') . " a " . $rdv->getDateHeureFin()->format('H:i')
                );
            }
        }
    }

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
