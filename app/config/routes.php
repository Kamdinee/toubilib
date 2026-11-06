<?php
declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;

// declaration des routes de l'API
return function (App $app): App {

    $app->group('/rdvs', function (RouteCollectorProxy $group) {
        // annulation d'un rendez-vous : modification partielle de la ressource
        // PATCH /rdvs/{id} avec le corps {"status": "annule"}
        $group->patch('/{id}', AnnulerRendezVousAction::class)->setName('rdv-annuler');
    });

    return $app;
};
