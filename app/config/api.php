<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\application\ports\api\ServiceRendezVousInterface;

// definitions des actions de l'application Slim
return [

    AnnulerRendezVousAction::class => function (ContainerInterface $c) {
        return new AnnulerRendezVousAction($c->get(ServiceRendezVousInterface::class));
    },
];
