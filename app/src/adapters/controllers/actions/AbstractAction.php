<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\ValidatorBuilder as v;
use Slim\Exception\HttpBadRequestException;
use toubilib\adapters\controllers\middlewares\JsonRenderer;

abstract class AbstractAction {

    abstract public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface;

    protected function json(ResponseInterface $rs, mixed $data, int $status = 200): ResponseInterface {
        return JsonRenderer::render($rs, $status, $data);
    }

    /**
     * Lit la periode demandee dans les parametres debut et fin (format AAAA-MM-JJ).
     * Par defaut : la journee courante. Si seul debut est fourni, la periode est cette journee.
     *
     * @return DateTimeImmutable[] [debut a 00:00:00, fin a 23:59:59]
     * @throws HttpBadRequestException si une date est mal formee ou si fin precede debut
     */
    protected function lirePeriode(ServerRequestInterface $rq): array {
        $params = $rq->getQueryParams();
        $debut = $params['debut'] ?? date('Y-m-d');
        $fin = $params['fin'] ?? $debut;

        foreach (['debut' => $debut, 'fin' => $fin] as $nom => $valeur) {
            if (!v::stringType()->date('Y-m-d')->isValid($valeur)) {
                throw new HttpBadRequestException($rq, "Le parametre $nom doit etre une date au format AAAA-MM-JJ");
            }
        }
        if ($fin < $debut) {
            throw new HttpBadRequestException($rq, 'La date de fin doit etre posterieure ou egale a la date de debut');
        }

        return [new DateTimeImmutable("$debut 00:00:00"), new DateTimeImmutable("$fin 23:59:59")];
    }

    protected function empty(ResponseInterface $rs, int $status = 204): ResponseInterface {
        return JsonRenderer::render($rs, $status, null);
    }
}

