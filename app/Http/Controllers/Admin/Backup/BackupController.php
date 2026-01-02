<?php

namespace App\Http\Controllers\Admin\Backup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\BackupDestination\Backup;
use App\Jobs\RunBackupJob; // see job below (recommended)
use Exception;
use App\Enums\ViewPaths\Admin\BackupRestore;


class BackupController extends Controller
{
    // NOTE: backupPath is not used to query Spatie backups; Spatie knows disk + name
    // but we keep it if you want to build raw storage paths.
    protected string $backupPath = 'Laravel';

    public function index()
    {
        return $this->backupRestore();
    }

protected function backupRestore()
{
    $disk = config('backup.destination.disks')[0] ?? 'local';
    $backupName = config('backup.name', env('APP_NAME', 'laravel-backup'));

    $backupDestination = BackupDestination::create($disk, $backupName);
    $backups = $backupDestination->backups();

    $encryptionEnabled = !empty(config('backup.backup.password'));

    $files = $backups->map(function (Backup $backup, $key) use ($encryptionEnabled) {
        return [
            'id' => $key + 1,
            'file' => $backup->path(),
            'name' => basename($backup->path()),
            'size' => round($backup->sizeInBytes() / 1024 / 1024, 2) . ' MB',
            'date' => $backup->date()->format('d M Y - h:i A'),
            'encrypted' => $encryptionEnabled ? 'Yes' : 'No',
        ];
    })->sortByDesc('date')->values();

    return view(BackupRestore::INDEX[VIEW], [
        'backups' => $files,
        'totalBackups' => $files->count(),
        'lastBackup' => $files->first()['date'] ?? 'Never',
    ]);
}


    public function run(Request $request)
    {
        $type = $request->input('type', 'full'); 
        $encryption = $request->input('encryption', 'disable');
        try {
            if (config('queue.default') !== 'sync') {
                RunBackupJob::dispatch($type, $encryption === 'enable' ? env('BACKUP_ARCHIVE_PASSWORD') : null);
                return back()->with('success', 'Backup queued — running in background.');
            }

            $command = $this->buildCommand($type, $encryption === 'enable');
            Artisan::call($command);

            return back()->with('success', 'Backup successfully created!');
        } catch (Exception $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    protected function buildCommand(string $type, bool $usePassword = false): string
    {
        if ($type === 'db') {
            $cmd = 'backup:run --only-db';
        } elseif ($type === 'files') {
            $cmd = 'backup:run --only-files';
        } else {
            $cmd = 'backup:run';
        }

        if ($usePassword && env('BACKUP_ARCHIVE_PASSWORD')) {
            $cmd .= ' --password="' . addslashes(env('BACKUP_ARCHIVE_PASSWORD')) . '"';
        }

        return $cmd;
    }
public function download($file)
{
    // $file aa raha hai jaise: Buio/backup-2025-12-08-...
    $fullPath = $file; // pura path use karo, prefix mat lagao!

    if (!Storage::disk('local')->exists($fullPath)) {
        abort(404, 'Backup file not found.');
    }

    return Storage::disk('local')->download($fullPath, basename($file));
}

public function delete($file)
{
    $fullPath = $file;

    if (Storage::disk('local')->exists($fullPath)) {
        Storage::disk('local')->delete($fullPath);
    }

    return back()->with('success', 'Backup deleted successfully!');
}
}
