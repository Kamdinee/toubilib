<?php
declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use toubilib\adapters\controllers\actions\AfficherPraticienAction;
use toubilib\adapters\controllers\actions\AfficherRendezVousAction;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\adapters\controllers\actions\CreerRendezVousAction;
use toubilib\adapters\controllers\actions\HonorerRendezVousAction;
use toubilib\adapters\controllers\actions\IgnorerRendezVousAction;
use toubilib\adapters\controllers\actions\ListerPraticiensAction;
use toubilib\adapters\controllers\middlewares\ValidationCreateRdvMiddleware;

// declaration des routes de l'API
return function (App $app): App {

    $app->group('/praticiens', function (RouteCollectorProxy $group) {
        // fonctionnalite 1 : lister les praticiens
        $group->get('', ListerPraticiensAction::class)->setName('praticiens-lister');
        // fonctionnalite 2 : detail d'un praticien
        $group->get('/{id}', AfficherPraticienAction::class)->setName('praticien-afficher');
    });

    $app->group('/rdvs', function (RouteCollectorProxy $group) {
        // fonctionnalite 5 : creer un rendez-vous
        $group->post('', CreerRendezVousAction::class)
            ->add(ValidationCreateRdvMiddleware::class)
            ->setName('rdv-creer');
        // fonctionnalite 3 : detail d'un rendez-vous
        $group->get('/{id}', AfficherRendezVousAction::class)->setName('rdv-afficher');
        // fonctionnalite 6 : annuler un rendez-vous (changement d'etat du RDV)
        $group->patch('/{id}/annuler', AnnulerRendezVousAction::class)->setName('rdv-annuler');
        // fonctionnalite 9 : marquer un rendez-vous comme honore ou ignore
        $group->patch('/{id}/honorer', HonorerRendezVousAction::class)->setName('rdv-honorer');
        $group->patch('/{id}/ignorer', IgnorerRendezVousAction::class)->setName('rdv-ignorer');
    });

    return $app;
};
