<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

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

        Artisan::call($command);
    }
}
