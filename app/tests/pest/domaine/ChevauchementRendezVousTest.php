<?php
declare(strict_types=1);

use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\RendezVousStatus;

// RDV le 7 janvier 2030 a l'heure donnee, pour la duree donnee
function rdvA(string $heure, int $duree, RendezVousStatus $status = RendezVousStatus::PREVU): RendezVous {
    return new RendezVous(uniqid(), 'prat-1', 'pat-1', new DateTimeImmutable("2030-01-07 $heure"), $duree, status: $status);
}

it('devrait detecter un RDV qui commence pendant un autre', function () {
    // 10:00-10:30 et 10:15-10:35
    expect(rdvA('10:15', 20)->chevauche(rdvA('10:00', 30)))->toBeTrue()
        ->and(rdvA('10:00', 30)->chevauche(rdvA('10:15', 20)))->toBeTrue();
});

it('devrait detecter un RDV qui finit pendant un autre', function () {
    // 09:50-10:10 et 10:00-10:30
    expect(rdvA('09:50', 20)->chevauche(rdvA('10:00', 30)))->toBeTrue();
});

it('devrait detecter un RDV inclus dans un autre et deux RDV identiques', function () {
    expect(rdvA('10:05', 15)->chevauche(rdvA('10:00', 30)))->toBeTrue()
        ->and(rdvA('10:00', 30)->chevauche(rdvA('10:00', 30)))->toBeTrue();
});

it('ne devrait pas detecter de chevauchement pour des RDV qui se suivent', function () {
    // 10:00-10:30 puis 10:30-10:50
    expect(rdvA('10:30', 20)->chevauche(rdvA('10:00', 30)))->toBeFalse()
        ->and(rdvA('09:40', 20)->chevauche(rdvA('10:00', 30)))->toBeFalse();
});

it('ne devrait pas detecter de chevauchement pour des RDV eloignes', function () {
    expect(rdvA('14:00', 30)->chevauche(rdvA('10:00', 30)))->toBeFalse();
});

it('ne devrait pas tenir compte d\'un RDV annule', function () {
    expect(rdvA('10:00', 30)->chevauche(rdvA('10:00', 30, RendezVousStatus::ANNULE)))->toBeFalse();
});
