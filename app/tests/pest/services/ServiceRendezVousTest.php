<?php
declare(strict_types=1);

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use tests\pest\fakes\InMemoryRendezVousRepository;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\usecases\ServiceRendezVous;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\RendezVousStatus;
use toubilib\domain\exceptions\RendezVousNotFoundException;
use toubilib\domain\exceptions\StatusChangeNotAllowedException;

it('devrait annuler un rendez-vous avec succes via le service', function () {
    // preparer le repository avec un RDV futur
    $repository = new InMemoryRendezVousRepository();
    $id = Uuid::uuid4()->toString();
    $rdv = new RendezVous($id, 'prat-1', 'pat-1', new DateTimeImmutable('+3 days'));
    $repository->save($rdv);

    // instancier le service
    $service = new ServiceRendezVous($repository);

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
    $service = new ServiceRendezVous($repository);

    expect(fn () => $service->annuler('id-inexistant'))
        ->toThrow(RendezVousNotFoundException::class);
});

it('devrait lever une StatusChangeNotAllowedException si le RDV est dans le passe', function () {
    $repository = new InMemoryRendezVousRepository();
    $id = Uuid::uuid4()->toString();
    $rdvPasse = new RendezVous($id, 'prat-1', 'pat-1', new DateTimeImmutable('-2 days'));
    $repository->save($rdvPasse);

    $service = new ServiceRendezVous($repository);

    expect(fn () => $service->annuler($id))
        ->toThrow(StatusChangeNotAllowedException::class);
});

