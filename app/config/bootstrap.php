<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;

// chargement des variables d'environnement
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// creation du conteneur d'injection de dependances
$builder = new ContainerBuilder();
$builder->addDefinitions(__DIR__ . '/settings.php');
$builder->addDefinitions(__DIR__ . '/services.php');
$builder->addDefinitions(__DIR__ . '/api.php');
$container = $builder->build();

// creation de l'application Slim utilisant ce conteneur
$app = AppFactory::createFromContainer($container);

// ajout des middlewares
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// gestionnaire d'erreurs au format JSON
$errorMiddleware = $app->addErrorMiddleware($container->get('displayErrorDetails'), false, false);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType('application/json');
$errorHandler->registerErrorRenderer('application/json', function ($exception, $displayErrorDetails) {
    return json_encode([
        'error' => $exception->getMessage()
    ], JSON_UNESCAPED_UNICODE);
});

// declaration des routes de l'API
$app->group('/rdvs', function (\Slim\Routing\RouteCollectorProxy $group) {
    // annulation d'un rendez-vous : modification partielle de la ressource
    // PATCH /rdvs/{id} avec le corps {"status": "annule"}
    $group->patch('/{id}', AnnulerRendezVousAction::class)->setName('rdv-annuler');
});

return $app;
