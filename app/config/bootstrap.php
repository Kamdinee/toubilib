<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;

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
$app = (require __DIR__ . '/routes.php')($app);

return $app;
