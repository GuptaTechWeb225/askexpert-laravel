<?php

namespace App\Enums\ViewPaths\Admin;

enum BackupRestore
{
    const INDEX = [
        URI => 'index',
        VIEW => 'admin-views.backup.index'
    ];
    const DOWNLOAD = [
        URI => 'download',
        VIEW => 'admin-views.profile.update-view'
    ];
    const DELETE = [
        URI => 'delete',
        VIEW => 'admin-views.profile.update-view'
    ];
    const RUN = [
        URI => 'run',
        VIEW => 'admin-views.profile.update-view'
    ];
}
