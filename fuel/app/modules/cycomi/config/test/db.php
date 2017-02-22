<?php
/**
 * The test database settings. These get merged with the global settings.
 *
 * This environment is primarily used by unit tests, to run on a controlled environment.
 */

return [
    'user_master' => [
        'connection' => [
            'hostname' => getenv('DOCKERTEST_DB_1_PORT_3306_TCP_ADDR'),
            'username' => 'linku',
            'password' => 'linku',
        ],
        'profiling'  => true,
    ],
    'user_slave' => [
        'connection' => [
            'hostname' => getenv('DOCKERTEST_DB_1_PORT_3306_TCP_ADDR'),
            'username' => 'linku',
            'password' => 'linku',
        ],
        'profiling'  => true,
    ],
    'manga_master' => [
        'connection' => [
            'hostname' => getenv('DOCKERTEST_DB_1_PORT_3306_TCP_ADDR'),
            'username' => 'linku',
            'password' => 'linku',
        ],
        'profiling'  => true,
    ],
    'manga_slave' => [
        'connection' => [
            'hostname' => getenv('DOCKERTEST_DB_1_PORT_3306_TCP_ADDR'),
            'username' => 'linku',
            'password' => 'linku',
        ],
        'profiling'  => true,
    ],
    'log_master' => [
        'connection' => [
            'hostname' => getenv('DOCKERTEST_DB_1_PORT_3306_TCP_ADDR'),
            'username' => 'linku',
            'password' => 'linku',
        ],
        'profiling'  => true,
    ],
    'log_slave' => [
        'connection' => [
            'hostname' => getenv('DOCKERTEST_DB_1_PORT_3306_TCP_ADDR'),
            'username' => 'linku',
            'password' => 'linku',
        ],
        'profiling'  => true,
    ],
];
