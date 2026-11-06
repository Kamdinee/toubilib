<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use toubilib\adapters\persistence\PraticienRepository;
use toubilib\adapters\persistence\RendezVousRepository;
use toubilib\application\ports\api\ServicePraticienInterface;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\application\usecases\ServicePraticien;
use toubilib\application\usecases\ServiceRendezVous;

// definitions des services et des repositorys
return [

    // connexion PDO a la base toubilib
    'toubilib.pdo' => function (ContainerInterface $c) {
        $dsn = "{$c->get('db.driver')}:host={$c->get('db.host')};dbname={$c->get('db.database')}";

        return new PDO($dsn, $c->get('db.username'), $c->get('db.password'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    },

    // repositorys
    RendezVousRepositoryInterface::class => function (ContainerInterface $c) {
        return new RendezVousRepository($c->get('toubilib.pdo'));
    },

    PraticienRepositoryInterface::class => function (ContainerInterface $c) {
        return new PraticienRepository($c->get('toubilib.pdo'));
    },

    // services metier
    ServiceRendezVousInterface::class => function (ContainerInterface $c) {
        return new ServiceRendezVous($c->get(RendezVousRepositoryInterface::class));
    },

    ServicePraticienInterface::class => function (ContainerInterface $c) {
        return new ServicePraticien($c->get(PraticienRepositoryInterface::class));
    },
];
