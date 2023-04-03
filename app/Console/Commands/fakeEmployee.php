<?php

namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;

class fakeEmployee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fake:employee';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert 1000 rows of fake data into employees table.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Employee::factory()->count(1000)->create();
        // return Command::SUCCESS;
    }
}
