<?php
declare(strict_types=1);

use tests\pest\fakes\InMemoryPraticienRepository;
use toubilib\application\dto\PraticienDetailDTO;
use toubilib\application\dto\PraticienDTO;
use toubilib\application\usecases\ServicePraticien;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\Specialite;
use toubilib\domain\exceptions\PraticienNotFoundException;

function creerRepositoryPraticiens(): InMemoryPraticienRepository {
    $generaliste = new Specialite(1, 'médecine générale', 'Médecine Générale');
    $pediatre = new Specialite(3, 'pédiatrie', 'Maladies des enfants');

    $repository = new InMemoryPraticienRepository();
    $repository->add(new Praticien('prat-1', 'Paul', 'Marine', 'Paris', 'marine.paul@mail.fr', '02 36 11 25 88', $generaliste, 'Dr.', '98537711172', true));
    $repository->add(new Praticien('prat-2', 'Durand', 'Luc', 'Nancy', 'luc.durand@mail.fr', '03 83 00 00 00', $pediatre, 'Dr.', null, false));
    return $repository;
}

it('devrait lister tous les praticiens sous forme de PraticienDTO', function () {
    $service = new ServicePraticien(creerRepositoryPraticiens());

    $praticiens = $service->listerPraticiens();

    expect($praticiens)->toHaveCount(2)
        ->and($praticiens[0])->toBeInstanceOf(PraticienDTO::class)
        ->and($praticiens[0]->nom)->toBe('Paul')
        ->and($praticiens[0]->specialite)->toBe('médecine générale')
        ->and($praticiens[1]->ville)->toBe('Nancy');
});

it('devrait retourner une liste vide s\'il n\'y a aucun praticien', function () {
    $service = new ServicePraticien(new InMemoryPraticienRepository());

    expect($service->listerPraticiens())->toBe([]);
});

it('devrait afficher le detail d\'un praticien', function () {
    $service = new ServicePraticien(creerRepositoryPraticiens());

    $dto = $service->afficherPraticien('prat-2');

    expect($dto)->toBeInstanceOf(PraticienDetailDTO::class)
        ->and($dto->titre)->toBe('Dr.')
        ->and($dto->nom)->toBe('Durand')
        ->and($dto->prenom)->toBe('Luc')
        ->and($dto->specialite)->toBe('pédiatrie')
        ->and($dto->telephone)->toBe('03 83 00 00 00')
        ->and($dto->accepteNouveauPatient)->toBeFalse();

    $array = $dto->toArray();
    expect($array['contacts']['email'])->toBe('luc.durand@mail.fr')
        ->and($array['adresse']['ville'])->toBe('Nancy');
});

it('devrait lever une PraticienNotFoundException si le praticien n\'existe pas', function () {
    $service = new ServicePraticien(creerRepositoryPraticiens());

    expect(fn () => $service->afficherPraticien('inconnu'))
        ->toThrow(PraticienNotFoundException::class);
});
