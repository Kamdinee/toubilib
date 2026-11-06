<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;
use toubilib\application\ports\api\ServicePraticienInterface;
use toubilib\domain\exceptions\PraticienNotFoundException;

class AfficherPraticienAction extends AbstractAction {

    public function __construct(
        private ServicePraticienInterface $servicePraticien
    ) {}

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface {
        $id = $args['id'] ?? '';

        try {
            $dto = $this->servicePraticien->afficherPraticien($id);
            $routes = RouteContext::fromRequest($rq)->getRouteParser();

            // 200 avec le praticien et ses liens HATEOAS
            return $this->json($rs, [
                'type' => 'ressource',
                'praticien' => $dto->toArray(),
                'links' => [
                    'self' => ['href' => $routes->urlFor('praticien-afficher', ['id' => $id])],
                    'rdvs' => ['href' => $routes->urlFor('praticien-agenda', ['id' => $id])],
                    'creneaux' => ['href' => $routes->urlFor('praticien-creneaux', ['id' => $id])],
                ],
            ], 200);

        } catch (PraticienNotFoundException $e) {
            // erreur 404 si le praticien est introuvable
            throw new HttpNotFoundException($rq, "Praticien $id not found");
        }
    }
}
