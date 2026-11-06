<?php
declare(strict_types=1);

use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\exceptions\DonneesInvalidesException;

function donneesRdvValides(): array {
    return [
        'praticien_id' => '4305f5e9-be5a-4ccf-8792-7e07d7017363',
        'patient_id' => 'da362a38-c1bf-39ed-8b2e-3bc99a86cfde',
        'date_heure_debut' => '2030-01-07 10:00',
        'motif_visite' => 'CI',
    ];
}

function erreursDTO(mixed $data): array {
    try {
        CreateRdvDTO::fromArray($data);
    } catch (DonneesInvalidesException $e) {
        return $e->getErreurs();
    }
    return [];
}

it('devrait creer le DTO a partir de donnees valides', function () {
    $dto = CreateRdvDTO::fromArray(donneesRdvValides());

    expect($dto->toArray())->toBe(donneesRdvValides());
});

it('devrait supprimer les espaces et les balises des donnees', function () {
    $data = donneesRdvValides();
    $data['motif_visite'] = '  <b>CI</b> ';

    expect(CreateRdvDTO::fromArray($data)->motifVisite)->toBe('CI');
});

it('devrait refuser un corps qui n\'est pas un tableau', function () {
    expect(fn () => CreateRdvDTO::fromArray(null))->toThrow(DonneesInvalidesException::class);
});

it('devrait signaler tous les champs absents', function () {
    expect(erreursDTO([]))->toHaveCount(4)
        ->toHaveKey('praticien_id')
        ->toHaveKey('patient_id')
        ->toHaveKey('date_heure_debut')
        ->toHaveKey('motif_visite');
});

it('devrait refuser un champ qui n\'est pas une chaine ou qui est vide', function () {
    $data = donneesRdvValides();
    $data['praticien_id'] = 12;
    $data['patient_id'] = '   ';

    expect(erreursDTO($data))->toHaveCount(2)
        ->toHaveKey('praticien_id')
        ->toHaveKey('patient_id');
});

it('devrait refuser un identifiant mal forme', function () {
    $data = donneesRdvValides();
    $data['patient_id'] = "x'; DROP TABLE rdv; --";

    expect(erreursDTO($data))->toHaveKey('patient_id');
});

it('devrait refuser une date au mauvais format ou inexistante', function () {
    foreach (['07/01/2030 10:00', '2030-01-07', '2030-02-30 10:00', '2030-01-07 25:00'] as $date) {
        $data = donneesRdvValides();
        $data['date_heure_debut'] = $date;
        expect(erreursDTO($data))->toHaveKey('date_heure_debut');
    }
});

it('devrait refuser un motif mal forme', function () {
    $data = donneesRdvValides();
    $data['motif_visite'] = 'consultation';

    expect(erreursDTO($data))->toHaveKey('motif_visite');
});
