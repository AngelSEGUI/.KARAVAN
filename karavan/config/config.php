<?php
/**
 * Configuration générale de l'application.
 * Pour passer sur MySQL : changer 'driver' et renseigner host / name / user / pass.
 */
return [
    'app' => [
        'name'  => 'Karavan',
        'debug' => true,
    ],
    'db' => [
        'driver' => 'sqlite',                                // 'sqlite' ou 'mysql'
        'path'   => ROOT_PATH . '/storage/karavan.sqlite',   // SQLite uniquement
        'host'   => '127.0.0.1',
        'name'   => 'karavan',
        'user'   => 'root',
        'pass'   => '',
    ],
];
