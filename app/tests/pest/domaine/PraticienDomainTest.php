<?php
declare(strict_types=1);

use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\Specialite;
use toubilib\domain\exceptions\RendezVousInvalideException;

function unPraticien(): Praticien {
    return new Praticien('prat-1', 'Paul', 'Marine', 'Paris', 'marine.paul@mail.fr', '0236112588', new Specialite(1, 'médecine générale'));
}

it('devrait accepter les motifs CI, CO et CS', function () {
    $praticien = unPraticien();

    expect($praticien->accepteMotif('CI'))->toBeTrue()
        ->and($praticien->accepteMotif('CO'))->toBeTrue()
        ->and($praticien->accepteMotif('CS'))->toBeTrue();
});

it('devrait refuser un motif inconnu', function () {
    expect(unPraticien()->accepteMotif('XX'))->toBeFalse()
        ->and(unPraticien()->accepteMotif('ci'))->toBeFalse();
});

it('devrait calculer la duree du RDV en fonction du motif', function () {
    $praticien = unPraticien();

    expect($praticien->dureeRdv('CI'))->toBe(30)
        ->and($praticien->dureeRdv('CO'))->toBe(20)
        ->and($praticien->dureeRdv('CS'))->toBe(15);
});

it('devrait lever une exception pour la duree d\'un motif inconnu', function () {
    expect(fn () => unPraticien()->dureeRdv('XX'))
        ->toThrow(RendezVousInvalideException::class);
});

it('devrait accepter un RDV un jour ouvrable dans la plage horaire', function () {
    // 7 janvier 2030 : un lundi
    expect(unPraticien()->accepteDate(new DateTimeImmutable('2030-01-07 10:00'), 30))->toBeTrue()
        ->and(unPraticien()->accepteDate(new DateTimeImmutable('2030-01-11 09:00'), 30))->toBeTrue()
        ->and(unPraticien()->accepteDate(new DateTimeImmutable('2030-01-07 18:30'), 30))->toBeTrue();
});

it('devrait refuser un RDV le week-end', function () {
    // 5 et 6 janvier 2030 : samedi et dimanche
    expect(unPraticien()->accepteDate(new DateTimeImmutable('2030-01-05 10:00'), 30))->toBeFalse()
        ->and(unPraticien()->accepteDate(new DateTimeImmutable('2030-01-06 10:00'), 30))->toBeFalse();
});

it('devrait refuser un RDV hors de la plage horaire', function () {
    $praticien = unPraticien();

    expect($praticien->accepteDate(new DateTimeImmutable('2030-01-07 08:30'), 30))->toBeFalse()
        ->and($praticien->accepteDate(new DateTimeImmutable('2030-01-07 19:00'), 15))->toBeFalse()
        // commence dans la plage mais finit apres 19h
        ->and($praticien->accepteDate(new DateTimeImmutable('2030-01-07 18:45'), 30))->toBeFalse();
});
