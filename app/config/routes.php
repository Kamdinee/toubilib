<?php
declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\adapters\controllers\actions\ListerPraticiensAction;

// declaration des routes de l'API
return function (App $app): App {

    $app->group('/praticiens', function (RouteCollectorProxy $group) {
        // fonctionnalite 1 : lister les praticiens
        $group->get('', ListerPraticiensAction::class)->setName('praticiens-lister');
    });

    $app->group('/rdvs', function (RouteCollectorProxy $group) {
        // annulation d'un rendez-vous : modification partielle de la ressource
        // PATCH /rdvs/{id} avec le corps {"status": "annule"}
        $group->patch('/{id}', AnnulerRendezVousAction::class)->setName('rdv-annuler');
    });

    return $app;
};
