# Toubilib

API RESTful de gestion de rendez-vous médicaux.
Projet du module Développement Web Serveur avancé (BUT Informatique S5, IUT Nancy-Charlemagne).

PHP 8.5, Slim 4, PHP-DI, PostgreSQL, Pest. Architecture hexagonale.

## Lancement

Il faut seulement Docker.

1. Créer les fichiers de configuration :

   ```bash
   cp toubilib.env.dist toubilib.env
   cp app/config/.env.dist app/config/.env
   ```

   Dans `toubilib.env`, mettre les mêmes identifiants que dans `app/config/.env` :

   ```
   POSTGRES_DB=toubilib
   POSTGRES_USER=user
   POSTGRES_PASSWORD=1234
   ```

2. Démarrer (Composer s'exécute tout seul dans le conteneur) :

   ```bash
   docker compose up -d --build
   ```

3. Créer la base :

   ```bash
   docker compose exec toubilib.db psql -U user -d toubilib -f /var/sql/toubilib.schema.sql
   docker compose exec toubilib.db psql -U user -d toubilib -f /var/sql/toubilib.data.sql
   ```

L'API répond sur http://localhost:7080. Adminer (pour voir la base) est sur http://localhost:8880.

## Tests

```bash
docker compose exec api.toubilib vendor/bin/pest
```

Le dossier `bruno/` contient une collection [Bruno](https://www.usebruno.com) pour tester chaque route
(environnement `local`).

## Routes

| Fonctionnalité | Route | Codes |
|---|---|---|
| Lister les praticiens | `GET /praticiens` | 200 |
| Détail d'un praticien | `GET /praticiens/{id}` | 200, 404 |
| Agenda d'un praticien | `GET /praticiens/{id}/rdvs?debut=AAAA-MM-JJ&fin=AAAA-MM-JJ` | 200, 400, 404 |
| Créneaux occupés d'un praticien | `GET /praticiens/{id}/creneaux?debut=AAAA-MM-JJ&fin=AAAA-MM-JJ` | 200, 400, 404 |
| Détail d'un RDV | `GET /rdvs/{id}` | 200, 404 |
| Créer un RDV | `POST /rdvs` | 201, 400, 409 |
| Annuler un RDV | `PATCH /rdvs/{id}/annuler` | 200, 404, 409 |
| Honorer / ignorer un RDV | `PATCH /rdvs/{id}/honorer`, `/ignorer` | 200, 404, 409 |

- Sans `debut` ni `fin`, la période est la journée en cours.
- Les réponses sont en JSON, avec des liens HATEOAS (`links`).
- 400 : données invalides. 404 : ressource introuvable. 409 : l'état du RDV ou du praticien empêche l'opération.

Corps de `POST /rdvs` :

```json
{
  "praticien_id": "4305f5e9-be5a-4ccf-8792-7e07d7017363",
  "patient_id": "da362a38-c1bf-39ed-8b2e-3bc99a86cfde",
  "date_heure_debut": "2030-01-08 14:00",
  "motif_visite": "CO"
}
```

Règles d'un RDV :

- motifs : `CI` 30 min, `CO` 20 min, `CS` 15 min ;
- du lundi au vendredi, entre 9h et 19h, dans le futur, sans chevaucher un autre RDV du praticien ;
- annuler : seulement avant la date ; honorer ou ignorer : seulement après le début ;
- un RDV n'est jamais supprimé.

## Architecture

Le code est dans `app/src/` :

| Dossier | Rôle |
|---|---|
| `domain/` | Entités (`RendezVous`, `Praticien`, `Patient`…) et règles métier |
| `application/` | Services (`ServiceRendezVous`, `ServicePraticien`), DTO, validateur, interfaces (ports) |
| `adapters/` | Actions Slim, middlewares, repositories PDO |

Le métier ne dépend que d'interfaces. Le conteneur PHP-DI branche les classes concrètes,
avec ses définitions dans `app/config/` : `settings.php` (configuration), `services.php`
(services et repositories), `api.php` (actions et middlewares). Les routes sont dans `routes.php`.

## Tableau de bord

| # | Fonctionnalité | État | Réalisé par |
|---|---|---|---|
| 1 | Lister les praticiens | Fait | |
| 2 | Détail d'un praticien | Fait | |
| 3 | Détail d'un RDV | Fait | |
| 4 | Agenda d'un praticien | Fait | |
| 5 | Créer un RDV | Fait | |
| 6 | Annuler un RDV | Fait | |
| 7 | Créneaux occupés d'un praticien | Fait | |
| 9 | Honorer / ignorer un RDV | Fait | |
