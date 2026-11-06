<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use toubilib\adapters\controllers\actions\AfficherPraticienAction;
use toubilib\adapters\controllers\actions\AfficherRendezVousAction;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\adapters\controllers\actions\CreerRendezVousAction;
use toubilib\adapters\controllers\actions\HonorerRendezVousAction;
use toubilib\adapters\controllers\actions\IgnorerRendezVousAction;
use toubilib\adapters\controllers\actions\ListerCreneauxAction;
use toubilib\adapters\controllers\actions\ListerPraticiensAction;
use toubilib\adapters\controllers\middlewares\ValidationCreateRdvMiddleware;
use toubilib\application\ports\api\ServicePraticienInterface;
use toubilib\application\ports\api\ServiceRendezVousInterface;

// definitions des actions et des middlewares de l'application Slim
return [

    // praticiens
    ListerPraticiensAction::class => function (ContainerInterface $c) {
        return new ListerPraticiensAction($c->get(ServicePraticienInterface::class));
    },

    AfficherPraticienAction::class => function (ContainerInterface $c) {
        return new AfficherPraticienAction($c->get(ServicePraticienInterface::class));
    },

    ListerCreneauxAction::class => function (ContainerInterface $c) {
        return new ListerCreneauxAction($c->get(ServiceRendezVousInterface::class));
    },

    // rendez-vous
    AfficherRendezVousAction::class => function (ContainerInterface $c) {
        return new AfficherRendezVousAction($c->get(ServiceRendezVousInterface::class));
    },

    CreerRendezVousAction::class => function (ContainerInterface $c) {
        return new CreerRendezVousAction($c->get(ServiceRendezVousInterface::class));
    },

    AnnulerRendezVousAction::class => function (ContainerInterface $c) {
        return new AnnulerRendezVousAction($c->get(ServiceRendezVousInterface::class));
    },

    HonorerRendezVousAction::class => function (ContainerInterface $c) {
        return new HonorerRendezVousAction($c->get(ServiceRendezVousInterface::class));
    },

    IgnorerRendezVousAction::class => function (ContainerInterface $c) {
        return new IgnorerRendezVousAction($c->get(ServiceRendezVousInterface::class));
    },

    // middlewares
    ValidationCreateRdvMiddleware::class => function (ContainerInterface $c) {
        return new ValidationCreateRdvMiddleware();
    },
];
