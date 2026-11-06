<?php
declare(strict_types=1);

use toubilib\domain\entities\Patient;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\RendezVousStatus;
use toubilib\domain\entities\Specialite;
use toubilib\domain\exceptions\PraticienIndisponibleException;
use toubilib\domain\exceptions\RendezVousInvalideException;

function praticienCreation(): Praticien {
    return new Praticien('prat-1', 'Paul', 'Marine', 'Paris', 'marine.paul@mail.fr', '0236112588', new Specialite(1, 'médecine générale'));
}

function patientCreation(): Patient {
    return new Patient('pat-1', 'Teixeira', 'Denis', '0155016382');
}

// date de reference : vendredi 4 janvier 2030 a midi
function maintenant(): DateTimeImmutable {
    return new DateTimeImmutable('2030-01-04 12:00');
}

it('devrait creer un RDV valide avec la duree du motif', function () {
    $rdv = RendezVous::creer(praticienCreation(), patientCreation(), new DateTimeImmutable('2030-01-07 10:00'), 'CO', maintenant());

    expect($rdv->getPraticienId())->toBe('prat-1')
        ->and($rdv->getPatientId())->toBe('pat-1')
        ->and($rdv->getMotifVisite())->toBe('CO')
        ->and($rdv->getDuree())->toBe(20)
        ->and($rdv->getDateHeureFin()->format('Y-m-d H:i'))->toBe('2030-01-07 10:20')
        ->and($rdv->getStatus())->toBe(RendezVousStatus::PREVU)
        ->and($rdv->getId())->toBeString();
});

it('devrait refuser un motif non accepte par le praticien', function () {
    expect(fn () => RendezVous::creer(praticienCreation(), patientCreation(), new DateTimeImmutable('2030-01-07 10:00'), 'XX', maintenant()))
        ->toThrow(RendezVousInvalideException::class);
});

it('devrait refuser une date passee', function () {
    expect(fn () => RendezVous::creer(praticienCreation(), patientCreation(), new DateTimeImmutable('2030-01-03 10:00'), 'CI', maintenant()))
        ->toThrow(RendezVousInvalideException::class, 'La date du rendez-vous doit etre dans le futur');
});

it('devrait refuser une date le week-end', function () {
    expect(fn () => RendezVous::creer(praticienCreation(), patientCreation(), new DateTimeImmutable('2030-01-05 10:00'), 'CI', maintenant()))
        ->toThrow(RendezVousInvalideException::class);
});

it('devrait refuser un RDV qui depasse la plage horaire', function () {
    expect(fn () => RendezVous::creer(praticienCreation(), patientCreation(), new DateTimeImmutable('2030-01-07 18:45'), 'CI', maintenant()))
        ->toThrow(RendezVousInvalideException::class);
});

it('devrait refuser un RDV si le praticien n\'est pas disponible', function () {
    $praticien = praticienCreation();
    $praticien->setRdvs([
        new RendezVous('r1', 'prat-1', 'pat-2', new DateTimeImmutable('2030-01-07 10:00'), 30),
    ]);

    expect(fn () => RendezVous::creer($praticien, patientCreation(), new DateTimeImmutable('2030-01-07 10:20'), 'CS', maintenant()))
        ->toThrow(PraticienIndisponibleException::class);
});

it('devrait creer un RDV juste apres un RDV existant du praticien', function () {
    $praticien = praticienCreation();
    $praticien->setRdvs([
        new RendezVous('r1', 'prat-1', 'pat-2', new DateTimeImmutable('2030-01-07 10:00'), 30),
    ]);

    $rdv = RendezVous::creer($praticien, patientCreation(), new DateTimeImmutable('2030-01-07 10:30'), 'CS', maintenant());

    expect($rdv->getDateHeureFin()->format('H:i'))->toBe('10:45');
});
