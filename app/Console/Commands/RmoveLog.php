<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RmoveLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to remove log files';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (Storage::exists('data.json')) {
            Storage::delete('data.json');
        }
    }
}
