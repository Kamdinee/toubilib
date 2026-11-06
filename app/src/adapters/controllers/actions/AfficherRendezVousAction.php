<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\domain\exceptions\RendezVousNotFoundException;

class AfficherRendezVousAction extends AbstractAction {

    public function __construct(
        private ServiceRendezVousInterface $serviceRendezVous
    ) {}

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface {
        $id = $args['id'] ?? '';

        try {
            $dto = $this->serviceRendezVous->consulter($id);
            return $this->json($rs, $dto->toArray(), 200);

        } catch (RendezVousNotFoundException $e) {
            // erreur 404 si le RDV est introuvable
            throw new HttpNotFoundException($rq, $e->getMessage());
        }
    }
}
