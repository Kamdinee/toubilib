<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\domain\exceptions\RendezVousNotFoundException;
use toubilib\domain\exceptions\StatusChangeNotAllowedException;

class AnnulerRendezVousAction extends AbstractAction {

    public function __construct(
        private ServiceRendezVousInterface $serviceRendezVous
    ) {}

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface {
        $id = $args['id'] ?? '';

        try {
            // appel du cas d'utilisation
            $dto = $this->serviceRendezVous->annuler($id);

            // reponse 200 avec le DTO sous forme de tableau JSON
            return $this->json($rs, $dto->toArray(), 200);

        } catch (RendezVousNotFoundException $e) {
            // erreur 404 si le RDV est introuvable
            throw new HttpNotFoundException($rq, $e->getMessage());

        } catch (StatusChangeNotAllowedException $e) {
            // erreur 400 si la regle metier refuse l'annulation
            throw new HttpBadRequestException($rq, $e->getMessage());
        }
    }
}

