<?php
declare(strict_types=1);

namespace toubilib\application\dto;

use Respect\Validation\ValidatorBuilder as v;
use toubilib\application\exceptions\DonneesInvalidesException;

/**
 * Donnees de creation d'un nouveau rendez-vous
 */
final class CreateRdvDTO {

    public const FORMAT_DATE = 'Y-m-d H:i';

    private const CHAMPS = ['praticien_id', 'patient_id', 'date_heure_debut', 'motif_visite'];

    public function __construct(
        public readonly string $praticienId,
        public readonly string $patientId,
        public readonly string $dateHeureDebut,
        public readonly string $motifVisite
    ) {}

    /**
     * Cree le DTO a partir des donnees recues dans la requete HTTP.
     * Les chaines sont d'abord filtrees (espaces et balises supprimes), puis la
     * presence, le type et le format de chaque champ sont controles avec Respect\Validation.
     *
     * @throws DonneesInvalidesException si une donnee est absente ou mal formee
     */
    public static function fromArray(mixed $data): self {
        if (!v::arrayType()->isValid($data)) {
            throw new DonneesInvalidesException(['corps' => 'le corps de la requete doit etre un objet JSON']);
        }

        // protection des donnees : suppression des espaces et des balises
        foreach (self::CHAMPS as $champ) {
            if (isset($data[$champ]) && is_string($data[$champ])) {
                $data[$champ] = trim(strip_tags($data[$champ]));
            }
        }

        // une regle de validation et un message d'erreur par champ
        $identifiant = v::stringType()->notBlank()->regex('/^[a-zA-Z0-9-]{1,64}$/');
        $regles = [
            'praticien_id' => [$identifiant, 'doit etre un identifiant valide'],
            'patient_id' => [$identifiant, 'doit etre un identifiant valide'],
            'date_heure_debut' => [v::stringType()->dateTime(self::FORMAT_DATE), 'doit etre une date au format AAAA-MM-JJ HH:MM'],
            'motif_visite' => [v::stringType()->regex('/^[A-Z]{2}$/'), 'doit etre un code de 2 lettres majuscules (ex : CI)'],
        ];

        $erreurs = [];
        foreach ($regles as $champ => [$regle, $message]) {
            if (!v::keyExists($champ)->isValid($data)) {
                $erreurs[$champ] = "le champ $champ est obligatoire";
            } elseif (!$regle->isValid($data[$champ])) {
                $erreurs[$champ] = "le champ $champ $message";
            }
        }

        if (!empty($erreurs)) {
            throw new DonneesInvalidesException($erreurs);
        }

        return new self(
            praticienId: $data['praticien_id'],
            patientId: $data['patient_id'],
            dateHeureDebut: $data['date_heure_debut'],
            motifVisite: $data['motif_visite']
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
