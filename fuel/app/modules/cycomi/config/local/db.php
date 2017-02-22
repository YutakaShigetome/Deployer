<?php
/**
 * The development database settings. These get merged with the global settings.
 */

return [
    'user_master' => [
        'connection' => [
            'hostname' => getenv('DOCKERLOCAL_DB_1_PORT_3306_TCP_ADDR'),
            'username' => 'linku',
            'password' => 'linku',
        ],
        'profiling'  => true,
    ],
    'user_slave' => [
        'connection' => [
            'hostname' => getenv('DOCKERLOCAL_DB_1_PORT_3306_TCP_ADDR'),
            'username' => 'linku',
            'password' => 'linku',
        ],
        'profiling'  => true,
    ],
    'manga_master' => [
        'connection' => [
            'hostname' => getenv('DOCKERLOCAL_DB_1_PORT_3306_TCP_ADDR'),
            'username' => 'linku',
            'password' => 'linku',
        ],
        'profiling'  => true,
    ],
    'manga_slave' => [
        'connection' => [
            'hostname' => getenv('DOCKERLOCAL_DB_1_PORT_3306_TCP_ADDR'),
            'username' => 'linku',
            'password' => 'linku',
        ],
        'profiling'  => true,
    ],
    'log_master' => [
        'connection' => [
            'hostname' => getenv('DOCKERLOCAL_DB_1_PORT_3306_TCP_ADDR'),
            'username' => 'linku',
            'password' => 'linku',
        ],
        'profiling'  => true,
    ],
    'log_slave' => [
        'connection' => [
            'hostname' => getenv('DOCKERLOCAL_DB_1_PORT_3306_TCP_ADDR'),
            'username' => 'linku',
            'password' => 'linku',
        ],
        'profiling'  => true,
    ],
];
