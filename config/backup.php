<?php

return [

    'name' => env('APP_NAME', 'laravel-backup'),

    'source' => [
        'files' => [
            'include' => [
                base_path(),
            ],

            'exclude' => [
                base_path('vendor'),
                base_path('node_modules'),
                storage_path('app/backup-temp'),
                storage_path('app/Buio'),
                storage_path('app/public'),
            ],

            'follow_links' => false,
            'ignore_unreadable_directories' => true,

            // ✅ IMPORTANT: null hi rehne do
            'relative_path' => null,
        ],

        'databases' => [
            env('DB_CONNECTION', 'mysql'),
        ],
    ],

    'destination' => [
        'filename_prefix' => 'backup-',

        'disks' => ['local'],

        // ✅ Compression yahin hoti hai
        'compression_method' => ZipArchive::CM_STORE, 
        'compression_level' => 0,
    ],

    'temporary_directory' => storage_path('app/backup-temp'),

    // ✅ Encryption optional
    'password' => env('BACKUP_ARCHIVE_PASSWORD', null),
    'encryption' => env('BACKUP_ARCHIVE_PASSWORD') ? 'default' : null,

    // ✅ Notifications fully disabled (no crash)
    'notifications' => [
        'notifications' => [],
    'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,
    ],

    'monitor_backups' => [
        [
            'name' => env('APP_NAME', 'laravel-backup'),
            'disks' => ['local'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 50000,
            ],
        ],
    ],

    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'default_strategy' => [
            'keep_all_backups_for_days' => 7,
            'keep_daily_backups_for_days' => 16,
            'keep_weekly_backups_for_weeks' => 8,
            'keep_monthly_backups_for_months' => 4,
            'keep_yearly_backups_for_years' => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 50000,
        ],
    ],
];
