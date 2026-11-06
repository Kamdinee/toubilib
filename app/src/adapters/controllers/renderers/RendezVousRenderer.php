<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\renderers;

use DateTimeImmutable;
use Slim\Interfaces\RouteParserInterface;
use toubilib\application\dto\RendezVousDTO;

/**
 * Mise en forme JSON d'un rendez-vous pour l'API, avec les liens HATEOAS
 */
class RendezVousRenderer {

    /**
     * Rendez-vous seul : {"type": "ressource", "rendez_vous": {...}, "links": {...}}
     */
    public static function ressource(RendezVousDTO $rdv, RouteParserInterface $routes): array {
        return [
            'type' => 'ressource',
            'rendez_vous' => self::donnees($rdv),
            'links' => self::liens($rdv, $routes),
        ];
    }

    /**
     * Donnees d'un rendez-vous, dates au format JJ/MM/AAAA
     */
    public static function donnees(RendezVousDTO $rdv): array {
        return [
            'id' => $rdv->id,
            'praticien_id' => $rdv->praticienId,
            'praticien_nom' => $rdv->praticienNom,
            'patient_id' => $rdv->patientId,
            'patient_nom' => $rdv->patientNom,
            'date_debut' => self::formater($rdv->dateHeureDebut, 'd/m/Y H:i'),
            'date_fin' => self::formater($rdv->dateHeureFin, 'd/m/Y H:i'),
            'date_creation' => self::formater($rdv->dateCreation, 'd/m/Y'),
            'duree' => $rdv->duree,
            'motif_visite' => $rdv->motifLibelle,
            'etat' => $rdv->statusLibelle,
        ];
    }

    /**
     * Liens HATEOAS d'un rendez-vous
     */
    public static function liens(RendezVousDTO $rdv, RouteParserInterface $routes): array {
        return [
            'self' => ['href' => $routes->urlFor('rdv-afficher', ['id' => $rdv->id])],
            'praticien' => ['href' => $routes->urlFor('praticien-afficher', ['id' => $rdv->praticienId])],
            'patient' => ['href' => '/patients/' . $rdv->patientId],
            'annuler' => ['href' => $routes->urlFor('rdv-annuler', ['id' => $rdv->id])],
            'honorer' => ['href' => $routes->urlFor('rdv-honorer', ['id' => $rdv->id])],
            'ignorer' => ['href' => $routes->urlFor('rdv-ignorer', ['id' => $rdv->id])],
        ];
    }

    /**
     * Convertit une date 'Y-m-d H:i:s' du DTO dans le format demande
     */
    private static function formater(?string $date, string $format): ?string {
        return $date !== null ? (new DateTimeImmutable($date))->format($format) : null;
    }
}
