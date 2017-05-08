<?php
return [
    /**
     * Debug Level:
     *
     * Production Mode:
     * false: No error messages, errors, or warnings shown.
     *
     * Development Mode:
     * true: Errors and warnings shown.
     */
    'debug' => filter_var(env('DEBUG', true), FILTER_VALIDATE_BOOLEAN),

    /**
     * Configure basic information about the application.
     *
     * - uuidRegexp - Regular expression used to validate the rfid uuid
     * - lastIdFile - Location of the file that stores the last recognized uuid
     */
    'App' => [
        'uuidRegexp' => '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
        'lastIdFile' => ROOT . DS . 'last_id',
    ],

    /**
     * Configuration of the remote server that is queried to receive the playlists
     *
     * - protocol - Which protocol to use (http or https)
     * - host - Hostname or IP-Adress of the remote host
     * - port - Remote port
     */
    'Server' => [
        'protocol' => 'http',
        'host' => 'localhost',
        'port' => '8765',
    ],

    /**
     * Rsync configuration
     */
    'Rsync' => [
        'source' => '~/Musik/Ripped',
        // 'sourceUsername' => 'dachande',
        'target' => '/tgramp/foo',
        // 'targetUsername' => 'dachande',
        'config' => [
            'options' => ['v', 'z', 'P', 'R'],
            'arguments' => [
                'delete' => true,
                'delete-before' => true,
                'delete-excluded' => true,
                'filter' => '-rs_*/@eaDir*',
                'include' => [
                    '*/',
                    '*.aac',
                    '*.aax',
                    '*.ape',
                    '*.flac',
                    '*.m4a',
                    '*.mp3',
                    '*.ogg',
                    '*.wav',
                    '*.wma',
                ],
                'exclude' => [
                    '*',
                ],
            ],
        ],
    ],
];
