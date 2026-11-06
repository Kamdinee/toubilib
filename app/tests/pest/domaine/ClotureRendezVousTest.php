<?php
declare(strict_types=1);

use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\RendezVousStatus;
use toubilib\domain\exceptions\StatusChangeNotAllowedException;

function rdvCloture(string $debut, RendezVousStatus $status = RendezVousStatus::PREVU): RendezVous {
    return new RendezVous('r1', 'prat-1', 'pat-1', new DateTimeImmutable($debut), 30, status: $status);
}

it('devrait honorer un RDV PREVU qui a eu lieu', function () {
    $rdv = rdvCloture('-2 hours');
    $rdv->honorer();

    expect($rdv->getStatus())->toBe(RendezVousStatus::HONORE);
});

it('devrait ignorer un RDV PREVU qui a eu lieu', function () {
    $rdv = rdvCloture('-1 day');
    $rdv->ignorer();

    expect($rdv->getStatus())->toBe(RendezVousStatus::IGNORE);
});

it('devrait refuser d\'honorer ou d\'ignorer un RDV futur', function () {
    expect(fn () => rdvCloture('+1 day')->honorer())->toThrow(StatusChangeNotAllowedException::class)
        ->and(fn () => rdvCloture('+1 day')->ignorer())->toThrow(StatusChangeNotAllowedException::class);
});

it('devrait refuser d\'honorer un RDV annule ou deja ignore', function () {
    expect(fn () => rdvCloture('-1 day', RendezVousStatus::ANNULE)->honorer())->toThrow(StatusChangeNotAllowedException::class)
        ->and(fn () => rdvCloture('-1 day', RendezVousStatus::IGNORE)->honorer())->toThrow(StatusChangeNotAllowedException::class);
});

it('devrait refuser d\'annuler un RDV honore', function () {
    $rdv = rdvCloture('-1 day');
    $rdv->honorer();

    expect(fn () => $rdv->annuler())->toThrow(StatusChangeNotAllowedException::class);
});
