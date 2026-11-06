<?php
declare(strict_types=1);

use Ramsey\Uuid\Uuid;
use toubilib\adapters\persistence\RendezVousRepository;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\RendezVousStatus;
use toubilib\domain\exceptions\RendezVousNotFoundException;

function getTestPdo(): PDO {
    $host = getenv('lib.host') ?: 'toubilib.db';
    $db = getenv('lib.database') ?: 'toubilib';
    $user = getenv('lib.username') ?: 'user';
    $pass = getenv('lib.password') ?: '1234';

    return new PDO("pgsql:host=$host;dbname=$db", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
}

it('devrait trouver un rendez-vous existant en base de donnees', function () {
    $pdo = getTestPdo();
    $repository = new RendezVousRepository($pdo);

    // insere un RDV de test
    $id = Uuid::uuid4()->toString();
    $debut = new DateTimeImmutable('+5 days');
    $rdv = new RendezVous($id, 'prat-test', 'pat-test', $debut, 30, null, null, 'CI');
    $repository->ajouter($rdv);

    // recherche par id
    $trouve = $repository->findById($id);

    expect($trouve->getId())->toBe($id)
        ->and($trouve->getPraticienId())->toBe('prat-test')
        ->and($trouve->getPatientId())->toBe('pat-test')
        ->and($trouve->getMotifVisite())->toBe('CI')
        ->and($trouve->getStatus())->toBe(RendezVousStatus::PREVU);
});

it('devrait mettre a jour le statut d\'un rendez-vous avec save', function () {
    $pdo = getTestPdo();
    $repository = new RendezVousRepository($pdo);

    $id = Uuid::uuid4()->toString();
    $debut = new DateTimeImmutable('+5 days');
    $rdv = new RendezVous($id, 'prat-test', 'pat-test', $debut);
    $repository->ajouter($rdv);

    // annule le RDV et sauvegarde
    $rdv->annuler();
    $repository->save($rdv);

    // recharge depuis la base
    $misAJour = $repository->findById($id);
    expect($misAJour->getStatus())->toBe(RendezVousStatus::ANNULE);
});

it('devrait lever RendezVousNotFoundException si l\'identifiant n\'existe pas', function () {
    $pdo = getTestPdo();
    $repository = new RendezVousRepository($pdo);

    expect(fn () => $repository->findById('id-totalement-inconnu'))
        ->toThrow(RendezVousNotFoundException::class);
});

