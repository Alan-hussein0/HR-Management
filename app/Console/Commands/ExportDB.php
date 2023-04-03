<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExportDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:DB';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to export database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return Command::SUCCESS;
    }
}
