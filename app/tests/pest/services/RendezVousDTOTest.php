<?php
declare(strict_types=1);

use toubilib\application\dto\RendezVousDTO;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\RendezVousStatus;

it('devrait créer un RendezVousDTO correctement depuis une entité RendezVous', function () {
    $debut = new DateTimeImmutable('2026-10-15 10:00:00');
    $rdv = new RendezVous(
        id: 'rdv-123',
        praticienId: 'prat-456',
        patientId: 'pat-789',
        dateHeureDebut: $debut,
        duree: 30,
        motifVisite: 'CS'
    );

    $dto = RendezVousDTO::fromEntity($rdv);

    expect($dto->id)->toBe('rdv-123')
        ->and($dto->praticienId)->toBe('prat-456')
        ->and($dto->patientId)->toBe('pat-789')
        ->and($dto->dateHeureDebut)->toBe('2026-10-15 10:00:00')
        ->and($dto->dateHeureFin)->toBe('2026-10-15 10:30:00')
        ->and($dto->duree)->toBe(30)
        ->and($dto->motifVisite)->toBe('CS')
        ->and($dto->status)->toBe(RendezVousStatus::PREVU->value)
        ->and($dto->statusLibelle)->toBe('PREVU');

    $array = $dto->toArray();
    expect($array)->toBeArray()
        ->and($array['id'])->toBe('rdv-123')
        ->and($array['status_libelle'])->toBe('PREVU');
});

