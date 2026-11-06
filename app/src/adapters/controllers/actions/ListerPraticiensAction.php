<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\application\ports\api\ServicePraticienInterface;

class ListerPraticiensAction extends AbstractAction {

    public function __construct(
        private ServicePraticienInterface $servicePraticien
    ) {}

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface {
        $praticiens = array_map(
            fn ($dto) => $dto->toArray(),
            $this->servicePraticien->listerPraticiens()
        );

        return $this->json($rs, ['praticiens' => $praticiens], 200);
    }
}
