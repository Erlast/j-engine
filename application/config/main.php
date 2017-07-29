<?php
return [
    'db'          => require(APP_ROOT . "/config/db_params.php"),
    'routes'      => require(APP_ROOT . "/config/routes.php"),
    'basePath'    => APP_ROOT,
    'layout'      => 'main',
    'modulesPath' => APP_ROOT . "/modules",
    'modules'     => [
        'user' => []
    ]
];