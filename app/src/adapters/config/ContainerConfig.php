<?php
declare(strict_types=1);

namespace toubilib\adapters\config;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\adapters\persistence\RendezVousRepository;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\application\usecases\ServiceRendezVous;

final class ContainerConfig {

    public static function build(): \DI\Container {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([

            // connexion PDO a PostgreSQL
            'toubilib.pdo' => function (ContainerInterface $c) {
                $driver = $_ENV['lib.driver'] ?? 'pgsql';
                $host = $_ENV['lib.host'] ?? 'toubilib.db';
                $database = $_ENV['lib.database'] ?? 'toubilib';
                $user = $_ENV['lib.username'] ?? 'user';
                $password = $_ENV['lib.password'] ?? '1234';
                $dsn = "{$driver}:host={$host};dbname={$database}";

                return new \PDO($dsn, $user, $password, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                ]);
            },

            // adaptateur de persistance
            RendezVousRepositoryInterface::class => \DI\create(RendezVousRepository::class)
                ->constructor(\DI\get('toubilib.pdo')),

            // service applicatif
            ServiceRendezVousInterface::class => \DI\create(ServiceRendezVous::class)
                ->constructor(\DI\get(RendezVousRepositoryInterface::class)),

            // action de controleur Slim
            AnnulerRendezVousAction::class => \DI\create(AnnulerRendezVousAction::class)
                ->constructor(\DI\get(ServiceRendezVousInterface::class)),
        ]);

        return $builder->build();
    }
}

