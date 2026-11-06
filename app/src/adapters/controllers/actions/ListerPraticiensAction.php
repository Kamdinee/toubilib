<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteContext;
use toubilib\application\dto\PraticienDTO;
use toubilib\application\ports\api\ServicePraticienInterface;

class ListerPraticiensAction extends AbstractAction {

    public function __construct(
        private ServicePraticienInterface $servicePraticien
    ) {}

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface {
        $routes = RouteContext::fromRequest($rq)->getRouteParser();

        // chaque praticien est accompagne du lien vers son detail
        $praticiens = array_map(
            fn (PraticienDTO $dto) => [
                'praticien' => $dto->toArray(),
                'links' => ['self' => ['href' => $routes->urlFor('praticien-afficher', ['id' => $dto->id])]],
            ],
            $this->servicePraticien->listerPraticiens()
        );

        return $this->json($rs, [
            'type' => 'collection',
            'count' => count($praticiens),
            'praticiens' => $praticiens,
            'links' => ['self' => ['href' => $routes->urlFor('praticiens-lister')]],
        ], 200);
    }
}
