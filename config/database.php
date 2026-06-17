<?php
// config/database.php

return [
    'host'     => getenv('DB_HOST') ?: '127.0.0.1',
    'port'     => getenv('DB_PORT') ?: '5432',
    'dbname'   => getenv('DB_NAME') ?: 'gestion_stock',
    'user'     => getenv('DB_USER') ?: 'postgres',
    'password' => getenv('DB_PASS') ?: '',
    'charset'  => 'UTF8',
];