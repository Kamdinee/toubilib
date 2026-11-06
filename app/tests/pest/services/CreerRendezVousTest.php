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
