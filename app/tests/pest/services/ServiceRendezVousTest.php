<?php
declare(strict_types=1);

use Ramsey\Uuid\Uuid;
use tests\pest\fakes\InMemoryPatientRepository;
use tests\pest\fakes\InMemoryPraticienRepository;
use tests\pest\fakes\InMemoryRendezVousRepository;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\usecases\ServiceRendezVous;
use toubilib\application\validators\RdvValidator;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\RendezVousStatus;
use toubilib\domain\entities\Specialite;
use toubilib\domain\exceptions\PraticienNotFoundException;
use toubilib\domain\exceptions\RendezVousNotFoundException;
use toubilib\domain\exceptions\StatusChangeNotAllowedException;

function creerServiceRendezVous(InMemoryRendezVousRepository $repository): ServiceRendezVous {
    $praticiens = new InMemoryPraticienRepository();
    $patients = new InMemoryPatientRepository();
    return new ServiceRendezVous($repository, $praticiens, $patients, new RdvValidator($praticiens, $patients));
}

it('devrait retourner le detail d\'un rendez-vous', function () {
    $repository = new InMemoryRendezVousRepository();
    $id = Uuid::uuid4()->toString();
    $repository->save(new RendezVous($id, 'prat-1', 'pat-1', new DateTimeImmutable('2026-12-05 11:30:00'), 30, motifVisite: 'CI'));
    $service = creerServiceRendezVous($repository);

    $dto = $service->consulter($id);

    expect($dto)->toBeInstanceOf(RendezVousDTO::class)
        ->and($dto->id)->toBe($id)
        ->and($dto->praticienId)->toBe('prat-1')
        ->and($dto->patientId)->toBe('pat-1')
        ->and($dto->dateHeureDebut)->toBe('2026-12-05 11:30:00')
        ->and($dto->motifVisite)->toBe('CI')
        ->and($dto->statusLibelle)->toBe('PREVU');
});

it('devrait lever une RendezVousNotFoundException en consultant un RDV inexistant', function () {
    $service = creerServiceRendezVous(new InMemoryRendezVousRepository());

    expect(fn () => $service->consulter('id-inexistant'))
        ->toThrow(RendezVousNotFoundException::class);
});

it('devrait annuler un rendez-vous avec succes via le service', function () {
    // preparer le repository avec un RDV futur
    $repository = new InMemoryRendezVousRepository();
    $id = Uuid::uuid4()->toString();
    $rdv = new RendezVous($id, 'prat-1', 'pat-1', new DateTimeImmutable('+3 days'));
    $repository->save($rdv);

    // instancier le service
    $service = creerServiceRendezVous($repository);

    // executer l'annulation
    $dto = $service->annuler($id);

    // verifier le DTO retourne
    expect($dto)->toBeInstanceOf(RendezVousDTO::class)
        ->and($dto->id)->toBe($id)
        ->and($dto->status)->toBe(RendezVousStatus::ANNULE->value)
        ->and($dto->statusLibelle)->toBe('ANNULE');

    // verifier que l'entite a bien ete mise a jour dans le repository
    $rdvEnBase = $repository->findById($id);
    expect($rdvEnBase->getStatus())->toBe(RendezVousStatus::ANNULE);
});

it('devrait lever une RendezVousNotFoundException si le RDV n\'existe pas', function () {
    $repository = new InMemoryRendezVousRepository();
    $service = creerServiceRendezVous($repository);

    expect(fn () => $service->annuler('id-inexistant'))
        ->toThrow(RendezVousNotFoundException::class);
});

it('devrait lever une StatusChangeNotAllowedException si le RDV est dans le passe', function () {
    $repository = new InMemoryRendezVousRepository();
    $id = Uuid::uuid4()->toString();
    $rdvPasse = new RendezVous($id, 'prat-1', 'pat-1', new DateTimeImmutable('-2 days'));
    $repository->save($rdvPasse);

    $service = creerServiceRendezVous($repository);

    expect(fn () => $service->annuler($id))
        ->toThrow(StatusChangeNotAllowedException::class);
});


it('devrait honorer puis refuser d\'ignorer un RDV via le service', function () {
    $repository = new InMemoryRendezVousRepository();
    $repository->save(new RendezVous('r-passe', 'prat-1', 'pat-1', new DateTimeImmutable('-3 hours')));
    $service = creerServiceRendezVous($repository);

    expect($service->honorer('r-passe')->statusLibelle)->toBe('HONORE')
        ->and($repository->findById('r-passe')->getStatus())->toBe(RendezVousStatus::HONORE)
        ->and(fn () => $service->ignorer('r-passe'))->toThrow(StatusChangeNotAllowedException::class);
});

it('devrait lister les creneaux occupes d\'un praticien sur une periode, sans les RDV annules', function () {
    $repository = new InMemoryRendezVousRepository();
    $repository->save(new RendezVous('r1', 'prat-1', 'pat-1', new DateTimeImmutable('2030-01-07 14:00'), 20));
    $repository->save(new RendezVous('r2', 'prat-1', 'pat-2', new DateTimeImmutable('2030-01-07 09:00'), 30));
    $repository->save(new RendezVous('r3', 'prat-1', 'pat-3', new DateTimeImmutable('2030-01-07 10:00'), 30, status: RendezVousStatus::ANNULE));
    $repository->save(new RendezVous('r4', 'prat-1', 'pat-1', new DateTimeImmutable('2030-01-09 10:00'), 15));
    $repository->save(new RendezVous('r5', 'prat-2', 'pat-1', new DateTimeImmutable('2030-01-07 11:00'), 15));

    $praticiens = new InMemoryPraticienRepository();
    $praticiens->add(new Praticien('prat-1', 'Paul', 'Marine', 'Paris', 'm@mail.fr', '0102030405', new Specialite(1, 'médecine générale')));
    $patients = new InMemoryPatientRepository();
    $service = new ServiceRendezVous($repository, $praticiens, $patients, new RdvValidator($praticiens, $patients));

    $creneaux = $service->listerCreneauxOccupes('prat-1', new DateTimeImmutable('2030-01-07 00:00:00'), new DateTimeImmutable('2030-01-07 23:59:59'));

    expect($creneaux)->toHaveCount(2)
        ->and($creneaux[0]->dateHeureDebut)->toBe('2030-01-07 09:00:00')
        ->and($creneaux[0]->dateHeureFin)->toBe('2030-01-07 09:30:00')
        ->and($creneaux[1]->dateHeureDebut)->toBe('2030-01-07 14:00:00');
});

it('devrait lever PraticienNotFoundException pour les creneaux d\'un praticien inconnu', function () {
    $service = creerServiceRendezVous(new InMemoryRendezVousRepository());

    expect(fn () => $service->listerCreneauxOccupes('inconnu', new DateTimeImmutable(), new DateTimeImmutable()))
        ->toThrow(PraticienNotFoundException::class);
});
