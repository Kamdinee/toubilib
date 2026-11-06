<?php
declare(strict_types=1);

// definitions des variables de configuration
return [

    'displayErrorDetails' => true,

    // origine autorisee pour les requetes CORS ('*' : toutes les origines)
    'cors.origine' => $_ENV['cors.origine'] ?? '*',

    'db.driver'   => $_ENV['lib.driver'] ?? 'pgsql',
    'db.host'     => $_ENV['lib.host'] ?? 'toubilib.db',
    'db.database' => $_ENV['lib.database'] ?? 'toubilib',
    'db.username' => $_ENV['lib.username'] ?? 'user',
    'db.password' => $_ENV['lib.password'] ?? '1234',
];
