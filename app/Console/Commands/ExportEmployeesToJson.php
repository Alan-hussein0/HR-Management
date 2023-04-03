<?php

namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportEmployeesToJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:employee';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to export all employees to a json file.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $employee = Employee::all();
        if (Storage::exists('employee.json')) {
            Storage::delete('employee.json');
            Storage::put('employee.json', json_encode($employee));
        }
        Storage::put('employee.json', json_encode($employee));
    }
}
