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
     * - uuidRegexp      - Regular expression used to validate the rfid uuid
     * - audioFileRegexp - Regular expression used to filter out audio files from rsync output
     *                     used to build the playlist file.
     * - lastIdFile      - Location of the file that stores the last recognized uuid
     */
    'App' => [
        'uuidRegexp' => '/^[0-9a-f]{8}$/',
        'audioFileRegexp' => '/\.aac|\.aax|\.ape|\.flac|\.m4a|\.mp3|\.ogg|\.wav|\.wma$/',
        'lastUuidFile' => ROOT . DS . 'last_id',
        'pauseUuid' => '00000000',
        'invalidUuid' => 'ffffffff',
    ],

    /**
     * Configuration of the remote server that is queried to receive
     * the playlists.
     *
     * - protocol - Which protocol to use (http or https)
     * - host     - Hostname or IP-Adress of the remote host
     * - port     - Remote port
     */
    'Server' => [
        'protocol' => 'http',
        'host' => 'localhost',
        'port' => '8765',
    ],

    /**
     * Rsync configuration
     *
     * - source         - Source host
     * - sourceUsername - Username used for ssh login
     * - target         - Target folder
     *
     * !!DO NOT!! change anything else unless you know exactly what
     * you are doing. All rsync options and arguments have been
     * carefully set up to be most compatible.
     *
     * The filter is used to filter out the @eaDir directories from
     * my Synology DiskStation.
     *
     * If you want to add additional audio files, add them to the
     * include-section. Please make sure to add them to the
     * App.audioFileRegexp configurion as well.
     * Otherwise the audio files will be downloaded but not added
     * to the playlist.
     */
    'Rsync' => [
        'source' => 'remote.hostname',
        'sourceUsername' => 'username',
        'target' => '~/t00niebox/music',
        // 'targetUsername' => 'dachande',
        'config' => [
            'options' => ['v', 'z', 'r', 'P', 'R'],
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

    /**
     * MPD configuration
     *
     * - host      - Hostname of your MPD server. Should be set to localhost unless the
     *               MPD server is on a different machine.
     * - port      - Port of your MPD server.
     * - playlists - Path where MPD stores its playlists
     */
    'Mpd' => [
        'host' => 'localhost',
        'port' => 6600,
        'password' => 't00niebox',
        'playlists' => '/home/pi/.mpd/playlists',
    ],

    /**
     * Logging configuration
     */
    'Log' => [
        'debug' => [
            'className' => 'Cake\Log\Engine\ConsoleLog',
            'levels' => ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'],
        ],
    ],

    /**
     * Console colors
     */
    'Console' => [
        'styles' => [
            'debug' => ['text' => 'magenta'],
        ],
    ],
];
