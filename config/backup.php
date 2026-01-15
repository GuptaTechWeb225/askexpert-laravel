<?php

return [

    'name' => env('APP_NAME', 'laravel-backup'),

    'source' => [
        'files' => [
            'include' => [ base_path() ],
            'exclude' => [
                base_path('vendor'),
                base_path('node_modules'),
                storage_path('app/backup-temp'),
                storage_path('app/Buio'),
                storage_path('app/public'),
            ],
            'follow_links' => false,
            'ignore_unreadable_directories' => true,
            'relative_path' => null,
        ],

        'databases' => [
            env('DB_CONNECTION', 'mysql'),
        ],
    ],

    'destination' => [
        'filename_prefix' => 'backup-',
        'disks' => ['local'],
        'compression_method' => ZipArchive::CM_DEFLATE,
        'compression_level' => 9,
    ],

    'temporary_directory' => storage_path('app/backup-temp'),

    'zip' => [
        'password' => env('BACKUP_ARCHIVE_PASSWORD'),
        'encryption' => env('BACKUP_ARCHIVE_PASSWORD') ? 'default' : null,
    ],

    'notifications' => [
        'notifications' => [],
        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,
    ],

    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
    ],
];
