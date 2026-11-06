<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Ajoute les headers CORS a toutes les reponses de l'API,
 * y compris les reponses d'erreur et les requetes de pre-verification (OPTIONS)
 */
class CorsMiddleware implements MiddlewareInterface {

    /**
     * @param string $origineAutorisee origine autorisee, '*' pour toutes les origines
     */
    public function __construct(
        private string $origineAutorisee = '*'
    ) {}

    public function process(ServerRequestInterface $rq, RequestHandlerInterface $handler): ResponseInterface {
        $rs = $handler->handle($rq);

        // avec '*', on renvoie l'origine de la requete pour autoriser aussi les credentials
        $origine = $this->origineAutorisee === '*'
            ? ($rq->hasHeader('Origin') ? $rq->getHeaderLine('Origin') : '*')
            : $this->origineAutorisee;

        return $rs
            ->withHeader('Access-Control-Allow-Origin', $origine)
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PATCH, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Max-Age', '3600')
            ->withAddedHeader('Vary', 'Origin');
    }
}
