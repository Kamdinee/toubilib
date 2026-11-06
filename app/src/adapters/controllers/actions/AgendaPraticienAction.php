<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;
use toubilib\adapters\controllers\renderers\RendezVousRenderer;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\domain\exceptions\PraticienNotFoundException;

/**
 * Agenda d'un praticien sur une periode
 * GET /praticiens/{id}/rdvs?debut=AAAA-MM-JJ&fin=AAAA-MM-JJ
 */
class AgendaPraticienAction extends AbstractAction {

    public function __construct(
        private ServiceRendezVousInterface $serviceRendezVous
    ) {}

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface {
        $id = $args['id'] ?? '';
        [$debut, $fin] = $this->lirePeriode($rq);

        try {
            $agenda = $this->serviceRendezVous->agendaPraticien($id, $debut, $fin);
        } catch (PraticienNotFoundException $e) {
            throw new HttpNotFoundException($rq, "Praticien $id not found");
        }

        $routes = RouteContext::fromRequest($rq)->getRouteParser();
        $periode = ['debut' => $debut->format('Y-m-d'), 'fin' => $fin->format('Y-m-d')];

        return $this->json($rs, [
            'type' => 'collection',
            'count' => count($agenda),
            'periode' => $periode,
            'rendez_vous' => array_map(fn (RendezVousDTO $rdv) => [
                'rendez_vous' => RendezVousRenderer::donnees($rdv),
                'links' => ['self' => ['href' => $routes->urlFor('rdv-afficher', ['id' => $rdv->id])]],
            ], $agenda),
            'links' => [
                'self' => ['href' => $routes->urlFor('praticien-agenda', ['id' => $id], $periode)],
                'praticien' => ['href' => $routes->urlFor('praticien-afficher', ['id' => $id])],
                'creneaux' => ['href' => $routes->urlFor('praticien-creneaux', ['id' => $id], $periode)],
            ],
        ], 200);
    }
}
