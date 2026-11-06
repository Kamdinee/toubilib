<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;
use Slim\Routing\RouteContext;
use toubilib\adapters\controllers\middlewares\ValidationCreateRdvMiddleware;
use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\domain\exceptions\PraticienIndisponibleException;
use toubilib\domain\exceptions\RendezVousInvalideException;

class CreerRendezVousAction extends AbstractAction {

    public function __construct(
        private ServiceRendezVousInterface $serviceRendezVous
    ) {}

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface {
        // DTO construit et valide par ValidationCreateRdvMiddleware
        /** @var CreateRdvDTO $dto */
        $dto = $rq->getAttribute(ValidationCreateRdvMiddleware::ATTRIBUT);

        try {
            $rdv = $this->serviceRendezVous->creerRendezVous($dto);

        } catch (RendezVousInvalideException $e) {
            // 400 : praticien ou patient inexistant, motif ou date non acceptes
            throw new HttpBadRequestException($rq, $e->getMessage());

        } catch (PraticienIndisponibleException $e) {
            // 409 : le creneau demande est deja occupe
            throw new HttpException($rq, $e->getMessage(), 409);
        }

        // 201 Created avec l'URL du nouveau RDV dans le header Location
        $url = RouteContext::fromRequest($rq)->getRouteParser()->urlFor('rdv-afficher', ['id' => $rdv->id]);

        return $this->json($rs->withHeader('Location', $url), $rdv->toArray(), 201);
    }
}
