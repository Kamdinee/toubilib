<?php
declare(strict_types=1);

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\RendezVousStatus;
use toubilib\domain\exceptions\StatusChangeNotAllowedException;

it('devrait créer un rendez-vous avec le statut PREVU par défaut', function () {
    $id = Uuid::uuid4()->toString();
    $debut = new DateTimeImmutable('+1 day');
    $rdv = new RendezVous($id, 'prat-1', 'pat-1', $debut);

    expect($rdv->getStatus())->toBe(RendezVousStatus::PREVU)
        ->and($rdv->getId())->toBe($id)
        ->and($rdv->getDuree())->toBe(30);
});

it('devrait permettre d\'annuler un rendez-vous futur à l\'état PREVU', function () {
    $id = Uuid::uuid4()->toString();
    $debut = new DateTimeImmutable('+2 days');
    $rdv = new RendezVous($id, 'prat-1', 'pat-1', $debut);

    $rdv->annuler();

    expect($rdv->getStatus())->toBe(RendezVousStatus::ANNULE);
});

it('devrait rejeter l\'annulation d\'un rendez-vous dont la date est passée', function () {
    $id = Uuid::uuid4()->toString();
    $debut = new DateTimeImmutable('-1 hour');
    $rdv = new RendezVous($id, 'prat-1', 'pat-1', $debut);

    expect(fn () => $rdv->annuler())
        ->toThrow(StatusChangeNotAllowedException::class, "Impossible d'annuler un rendez-vous dont la date est déjà passée.");
});

it('devrait rejeter l\'annulation d\'un rendez-vous déjà ANNULE', function () {
    $id = Uuid::uuid4()->toString();
    $debut = new DateTimeImmutable('+2 days');
    $rdv = new RendezVous($id, 'prat-1', 'pat-1', $debut);

    $rdv->annuler();

    expect(fn () => $rdv->annuler())
        ->toThrow(StatusChangeNotAllowedException::class);
});

it('devrait rejeter l\'annulation d\'un rendez-vous déjà HONORE', function () {
    $id = Uuid::uuid4()->toString();
    $debut = new DateTimeImmutable('+2 days');
    $rdv = new RendezVous(
        id: $id,
        praticienId: 'prat-1',
        patientId: 'pat-1',
        dateHeureDebut: $debut,
        status: RendezVousStatus::HONORE
    );

    expect(fn () => $rdv->annuler())
        ->toThrow(StatusChangeNotAllowedException::class);
});

