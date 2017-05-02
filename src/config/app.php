<?php
return [
    'Server' => [
        'protocol' => 'http',
        'host' => 'localhost',
        'port' => '8765',
    ],

    'Rsync' => [
        'source' => '~/Musik/Ripped',
        // 'sourceUsername' => 'dachande',
        'target' => '/tmp/foo',
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

    'App' => [
        'lastIdFile' => ROOT . DS . 'last_id',
    ],
];
