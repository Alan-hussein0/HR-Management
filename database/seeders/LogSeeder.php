<?php

namespace Database\Seeders;

use App\Models\Log;
use Database\Seeders\Traits\DisableForeignKeys;
use Database\Seeders\Traits\TruncateTable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LogSeeder extends Seeder
{
    use TruncateTable, DisableForeignKeys;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    { 
        $this->disableForeignKeys();
        $this->truncate(table: 'profiles');
        Log::factory(10)->create();
        $this->enableForeignKeys();
    }
}
