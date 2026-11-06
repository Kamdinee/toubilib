<?php
declare(strict_types=1);

namespace toubilib\application\dto;

use DateTimeImmutable;
use toubilib\application\exceptions\DonneesInvalidesException;

/**
 * Donnees de creation d'un nouveau rendez-vous
 */
final class CreateRdvDTO {

    public const FORMAT_DATE = 'Y-m-d H:i';

    public function __construct(
        public readonly string $praticienId,
        public readonly string $patientId,
        public readonly string $dateHeureDebut,
        public readonly string $motifVisite
    ) {}

    /**
     * Cree le DTO a partir des donnees recues dans la requete HTTP,
     * en controlant la presence, le type et le format de chaque donnee
     * et en filtrant les chaines recues.
     *
     * @throws DonneesInvalidesException si une donnee est absente ou mal formee
     */
    public static function fromArray(mixed $data): self {
        if (!is_array($data)) {
            throw new DonneesInvalidesException(['corps' => 'le corps de la requete doit etre un objet JSON']);
        }

        $erreurs = [];
        $champs = ['praticien_id', 'patient_id', 'date_heure_debut', 'motif_visite'];
        $valeurs = [];

        // presence et type
        foreach ($champs as $champ) {
            if (!isset($data[$champ])) {
                $erreurs[$champ] = "le champ $champ est obligatoire";
            } elseif (!is_string($data[$champ])) {
                $erreurs[$champ] = "le champ $champ doit etre une chaine de caracteres";
            } else {
                // protection : suppression des espaces et des balises
                $valeurs[$champ] = trim(strip_tags($data[$champ]));
                if ($valeurs[$champ] === '') {
                    $erreurs[$champ] = "le champ $champ ne doit pas etre vide";
                }
            }
        }

        // format des identifiants
        foreach (['praticien_id', 'patient_id'] as $champ) {
            if (!isset($erreurs[$champ]) && !preg_match('/^[a-zA-Z0-9-]{1,64}$/', $valeurs[$champ])) {
                $erreurs[$champ] = "le champ $champ n'est pas un identifiant valide";
            }
        }

        // format de la date
        if (!isset($erreurs['date_heure_debut'])) {
            $date = DateTimeImmutable::createFromFormat(self::FORMAT_DATE, $valeurs['date_heure_debut']);
            if ($date === false || $date->format(self::FORMAT_DATE) !== $valeurs['date_heure_debut']) {
                $erreurs['date_heure_debut'] = 'le champ date_heure_debut doit etre au format AAAA-MM-JJ HH:MM';
            }
        }

        // format du motif
        if (!isset($erreurs['motif_visite']) && !preg_match('/^[A-Z]{2}$/', $valeurs['motif_visite'])) {
            $erreurs['motif_visite'] = 'le champ motif_visite doit etre un code de 2 lettres majuscules (ex : CI)';
        }

        if (!empty($erreurs)) {
            throw new DonneesInvalidesException($erreurs);
        }

        return new self(
            praticienId: $valeurs['praticien_id'],
            patientId: $valeurs['patient_id'],
            dateHeureDebut: $valeurs['date_heure_debut'],
            motifVisite: $valeurs['motif_visite']
        );
    }

    public function toArray(): array {
        return [
            'praticien_id' => $this->praticienId,
            'patient_id' => $this->patientId,
            'date_heure_debut' => $this->dateHeureDebut,
            'motif_visite' => $this->motifVisite,
        ];
    }
}
