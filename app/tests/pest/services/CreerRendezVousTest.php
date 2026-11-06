<?php
declare(strict_types=1);

use tests\pest\fakes\InMemoryPatientRepository;
use tests\pest\fakes\InMemoryPraticienRepository;
use tests\pest\fakes\InMemoryRendezVousRepository;
use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\usecases\ServiceRendezVous;
use toubilib\application\validators\RdvValidator;
use toubilib\domain\entities\Patient;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\Specialite;
use toubilib\domain\exceptions\RendezVousInvalideException;

/**
 * Service de creation avec un praticien 'prat-1' et un patient 'pat-1' connus
 */
function serviceCreationRdv(): ServiceRendezVous {
    $praticiens = new InMemoryPraticienRepository();
    $praticiens->add(new Praticien('prat-1', 'Paul', 'Marine', 'Paris', 'marine.paul@mail.fr', '0236112588', new Specialite(1, 'médecine générale')));

    $patients = new InMemoryPatientRepository();
    $patients->add(new Patient('pat-1', 'Teixeira', 'Denis', '0155016382'));

    return new ServiceRendezVous(new InMemoryRendezVousRepository(), new RdvValidator($praticiens, $patients));
}

it('devrait retourner les donnees recues si elles sont valides', function () {
    $dto = new CreateRdvDTO('prat-1', 'pat-1', '2030-01-07 10:00', 'CI');

    $resultat = serviceCreationRdv()->creerRendezVous($dto);

    expect($resultat->toArray())->toBe($dto->toArray());
});

it('devrait refuser un praticien qui n\'existe pas', function () {
    $dto = new CreateRdvDTO('prat-inconnu', 'pat-1', '2030-01-07 10:00', 'CI');

    expect(fn () => serviceCreationRdv()->creerRendezVous($dto))
        ->toThrow(RendezVousInvalideException::class, "Le praticien prat-inconnu n'existe pas");
});

it('devrait refuser un patient qui n\'existe pas', function () {
    $dto = new CreateRdvDTO('prat-1', 'pat-inconnu', '2030-01-07 10:00', 'CI');

    expect(fn () => serviceCreationRdv()->creerRendezVous($dto))
        ->toThrow(RendezVousInvalideException::class, "Le patient pat-inconnu n'existe pas");
});

it('devrait refuser un motif non accepte par le praticien', function () {
    $dto = new CreateRdvDTO('prat-1', 'pat-1', '2030-01-07 10:00', 'XX');

    expect(fn () => serviceCreationRdv()->creerRendezVous($dto))
        ->toThrow(RendezVousInvalideException::class);
});

it('devrait refuser une date au mauvais format', function () {
    $dto = new CreateRdvDTO('prat-1', 'pat-1', '07/01/2030 10h', 'CI');

    expect(fn () => serviceCreationRdv()->creerRendezVous($dto))
        ->toThrow(RendezVousInvalideException::class);
});

it('devrait refuser une date passee', function () {
    $dto = new CreateRdvDTO('prat-1', 'pat-1', '2020-01-06 10:00', 'CI');

    expect(fn () => serviceCreationRdv()->creerRendezVous($dto))
        ->toThrow(RendezVousInvalideException::class, 'La date du rendez-vous doit etre dans le futur');
});

it('devrait refuser une date non acceptee par le praticien', function () {
    // samedi 5 janvier 2030, puis un lundi a 20h
    $samedi = new CreateRdvDTO('prat-1', 'pat-1', '2030-01-05 10:00', 'CI');
    $soir = new CreateRdvDTO('prat-1', 'pat-1', '2030-01-07 20:00', 'CI');

    expect(fn () => serviceCreationRdv()->creerRendezVous($samedi))->toThrow(RendezVousInvalideException::class)
        ->and(fn () => serviceCreationRdv()->creerRendezVous($soir))->toThrow(RendezVousInvalideException::class);
});
