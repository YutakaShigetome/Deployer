<?php
/**
 * Use this file to override global defaults.
 *
 * See the individual environment DB configs for specific config information.
 */

return [
    'user_master' => [
        'type'         => 'mysqli',
        'connection'   => [
            'port'     => '3306',
            'database' => 'cy2_user',
            'compress' => false,
        ],
        'identifier'   => '`',
        'table_prefix' => '',
        'charset'      => 'utf8',
        'enable_cache' => false,
        'profiling'    => false,
        'readonly'     => ['user_slave'],
    ],
    'user_slave' => [
        'type'         => 'mysqli',
        'connection'   => [
            'port'     => '3306',
            'database' => 'cy2_user',
            'compress' => false,
        ],
        'identifier'   => '`',
        'table_prefix' => '',
        'charset'      => 'utf8',
        'enable_cache' => false,
        'profiling'    => false,
        'readonly'     => false,
    ],
    'manga_master' => [
        'type'         => 'mysqli',
        'connection'   => [
            'port'     => '3306',
            'database' => 'cy2_manga',
            'compress' => false,
        ],
        'identifier'   => '`',
        'table_prefix' => '',
        'charset'      => 'utf8',
        'enable_cache' => false,
        'profiling'    => false,
        'readonly'     => ['manga_slave'],
    ],
    'manga_slave' => [
        'type'         => 'mysqli',
        'connection'   => [
            'port'     => '3306',
            'database' => 'cy2_manga',
            'compress' => false,
        ],
        'identifier'   => '`',
        'table_prefix' => '',
        'charset'      => 'utf8',
        'enable_cache' => false,
        'profiling'    => false,
        'readonly'     => false,
    ],
    'log_master' => [
        'type'         => 'mysqli',
        'connection'   => [
            'port'     => '3306',
            'database' => 'cy2_log',
            'compress' => false,
        ],
        'identifier'   => '`',
        'table_prefix' => '',
        'charset'      => 'utf8',
        'enable_cache' => false,
        'profiling'    => false,
        'readonly'     => ['log_slave'],
    ],
    'log_slave' => [
        'type'         => 'mysqli',
        'connection'   => [
            'port'     => '3306',
            'database' => 'cy2_log',
            'compress' => false,
        ],
        'identifier'   => '`',
        'table_prefix' => '',
        'charset'      => 'utf8',
        'enable_cache' => false,
        'profiling'    => false,
        'readonly'     => false,
    ],
];
