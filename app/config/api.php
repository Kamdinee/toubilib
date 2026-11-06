<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use toubilib\adapters\controllers\actions\AfficherPraticienAction;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\adapters\controllers\actions\ListerPraticiensAction;
use toubilib\application\ports\api\ServicePraticienInterface;
use toubilib\application\ports\api\ServiceRendezVousInterface;

// definitions des actions de l'application Slim
return [

    // praticiens
    ListerPraticiensAction::class => function (ContainerInterface $c) {
        return new ListerPraticiensAction($c->get(ServicePraticienInterface::class));
    },

    AfficherPraticienAction::class => function (ContainerInterface $c) {
        return new AfficherPraticienAction($c->get(ServicePraticienInterface::class));
    },

    // rendez-vous
    AnnulerRendezVousAction::class => function (ContainerInterface $c) {
        return new AnnulerRendezVousAction($c->get(ServiceRendezVousInterface::class));
    },
];
