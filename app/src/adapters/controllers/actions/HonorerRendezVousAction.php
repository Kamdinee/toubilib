<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpException;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;
use toubilib\adapters\controllers\renderers\RendezVousRenderer;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\domain\exceptions\RendezVousNotFoundException;
use toubilib\domain\exceptions\StatusChangeNotAllowedException;

class HonorerRendezVousAction extends AbstractAction {

    public function __construct(
        private ServiceRendezVousInterface $serviceRendezVous
    ) {}

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface {
        $id = $args['id'] ?? '';

        try {
            // le RDV est marque comme honore
            $dto = $this->serviceRendezVous->honorer($id);

            // reponse 200 avec le rendez-vous et ses liens HATEOAS
            $routes = RouteContext::fromRequest($rq)->getRouteParser();
            return $this->json($rs, RendezVousRenderer::ressource($dto, $routes), 200);

        } catch (RendezVousNotFoundException $e) {
            // erreur 404 si le RDV est introuvable
            throw new HttpNotFoundException($rq, "Rendez-vous $id not found");

        } catch (StatusChangeNotAllowedException $e) {
            // erreur 409 Conflict : la requete est valide mais l'etat du RDV refuse le changement
            throw new HttpException($rq, $e->getMessage(), 409);
        }
    }
}

