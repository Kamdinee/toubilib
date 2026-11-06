<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
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
            return $this->json($rs, $dto->toArray(), 200);

        } catch (PraticienNotFoundException $e) {
            // erreur 404 si le praticien est introuvable
            throw new HttpNotFoundException($rq, $e->getMessage());
        }
    }
}
