<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

class RunBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $type;
    public $password;

    public function __construct(string $type = 'full', ?string $password = null)
    {
        $this->type = $type;
        $this->password = $password;
    }

    public function handle()
    {
        $command = match ($this->type) {
            'db' => 'backup:run --only-db',
            'files' => 'backup:run --only-files',
            default => 'backup:run',
        };

        if ($this->password) {
            $command .= ' --password="' . addslashes($this->password) . '"';
        }

        try {
            Artisan::call($command);
            $output = Artisan::output();

            Log::channel('backup')->info('Backup SUCCESS (Queue)', [
                'type' => $this->type,
                'command' => $command,
                'output' => $output,
            ]);
        } catch (Throwable $e) {

            Log::channel('backup')->error('Backup FAILED (Queue)', [
                'type' => $this->type,
                'command' => $command,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
    public function failed(Throwable $exception)
{
    Log::channel('backup')->critical('Backup JOB CRASHED', [
        'type' => $this->type,
        'error' => $exception->getMessage(),
    ]);
}

}
