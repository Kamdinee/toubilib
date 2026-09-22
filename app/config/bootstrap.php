<?php
declare(strict_types=1);

use Dotenv\Dotenv;
use Slim\Factory\AppFactory;
use toubilib\adapters\config\ContainerConfig;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;

// chargement des variables d'environnement
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// creation du conteneur d'injection de dependances
$container = ContainerConfig::build();
$app = AppFactory::createFromContainer($container);

// ajout des middlewares
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// gestionnaire d'erreurs au format JSON
$errorMiddleware = $app->addErrorMiddleware(true, false, false);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType('application/json');
$errorHandler->registerErrorRenderer('application/json', function ($exception, $displayErrorDetails) {
    return json_encode([
        'error' => $exception->getMessage()
    ], JSON_UNESCAPED_UNICODE);
});

// declaration des routes de l'API
$app->group('/rdvs', function (\Slim\Routing\RouteCollectorProxy $group) {
    // route d'annulation d'un rendez-vous avec PATCH et POST
    $group->patch('/{id}/annuler', AnnulerRendezVousAction::class)->setName('rdv-annuler-patch');
    $group->post('/{id}/annuler', AnnulerRendezVousAction::class)->setName('rdv-annuler-post');
});

return $app;

