<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;
use toubilib\application\dto\CreneauDTO;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\domain\exceptions\PraticienNotFoundException;

/**
 * Creneaux occupes d'un praticien sur une periode
 * GET /praticiens/{id}/creneaux?debut=AAAA-MM-JJ&fin=AAAA-MM-JJ
 */
class ListerCreneauxAction extends AbstractAction {

    public function __construct(
        private ServiceRendezVousInterface $serviceRendezVous
    ) {}

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface {
        $id = $args['id'] ?? '';
        [$debut, $fin] = $this->lirePeriode($rq);

        try {
            $creneaux = $this->serviceRendezVous->listerCreneauxOccupes($id, $debut, $fin);
        } catch (PraticienNotFoundException $e) {
            throw new HttpNotFoundException($rq, "Praticien $id not found");
        }

        $routes = RouteContext::fromRequest($rq)->getRouteParser();
        $periode = ['debut' => $debut->format('Y-m-d'), 'fin' => $fin->format('Y-m-d')];

        return $this->json($rs, [
            'type' => 'collection',
            'count' => count($creneaux),
            'periode' => $periode,
            'creneaux' => array_map(fn (CreneauDTO $c) => [
                'date_debut' => (new DateTimeImmutable($c->dateHeureDebut))->format('d/m/Y H:i'),
                'date_fin' => (new DateTimeImmutable($c->dateHeureFin))->format('d/m/Y H:i'),
            ], $creneaux),
            'links' => [
                'self' => ['href' => $routes->urlFor('praticien-creneaux', ['id' => $id], $periode)],
                'praticien' => ['href' => $routes->urlFor('praticien-afficher', ['id' => $id])],
            ],
        ], 200);
    }
}
