<?php

require ('public/index.php');

$migrations = [];
$seeds = [];
foreach ($modules as $module) {
    if (!\is_null($module::MIGRATIONS)) {
        $migrations[] = $module::MIGRATIONS;
    }
    if (!\is_null($module::SEEDS)) {
        $seeds[] = $module::SEEDS;
    }
}

return [
    'paths' => [
        'migrations' => $migrations,
        'seeds' => $seeds
    ],
    'environments' => [
        'default_database' => 'dev',
        'dev' => [
            'adapter' => 'mysql',
            'host' => $app->getContainer()->get('database.host'),
            'name' => $app->getContainer()->get('database.name'),
            'user' => $app->getContainer()->get('database.user'),
            'pass' => $app->getContainer()->get('database.pass'),
            'port' => $app->getContainer()->get('database.port'),
            'charset' => $app->getContainer()->get('database.charset')
        ]
    ],
];
