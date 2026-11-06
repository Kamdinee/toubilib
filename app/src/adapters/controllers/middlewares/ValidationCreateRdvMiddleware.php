<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpBadRequestException;
use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\exceptions\DonneesInvalidesException;

/**
 * Transforme les donnees recues dans la requete en CreateRdvDTO valide
 * et le transmet a l'action dans l'attribut 'createRdvDTO' de la requete.
 * Repond 400 si les donnees sont incompletes ou mal formees.
 */
class ValidationCreateRdvMiddleware implements MiddlewareInterface {

    public const ATTRIBUT = 'createRdvDTO';

    public function process(ServerRequestInterface $rq, RequestHandlerInterface $handler): ResponseInterface {
        try {
            $dto = CreateRdvDTO::fromArray($rq->getParsedBody());
        } catch (DonneesInvalidesException $e) {
            throw new HttpBadRequestException($rq, $e->getMessage());
        }

        return $handler->handle($rq->withAttribute(self::ATTRIBUT, $dto));
    }
}
