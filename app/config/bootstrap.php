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

// gestionnaire d'erreurs par defaut de Slim, reponses toujours au format JSON :
// {"message": "404 Not Found", "exception": [{"type", "code", "message", "file", "line"}]}
// (le detail "exception" n'est affiche que si displayErrorDetails vaut true)
$errorMiddleware = $app->addErrorMiddleware($container->get('displayErrorDetails'), false, false);
$errorMiddleware->getDefaultErrorHandler()->forceContentType('application/json');

// declaration des routes de l'API
$app = (require __DIR__ . '/routes.php')($app);

return $app;
