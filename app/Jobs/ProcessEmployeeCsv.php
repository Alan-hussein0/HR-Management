<?php

namespace App\Jobs;

use App\Models\Employee;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ProcessEmployeeCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $employees_info;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($employees_info)
    {
        $this->employees_info = $employees_info;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->employees_info as $key => $employee_info) {

            if($key == array_key_first($this->employees_info)){
                continue;
            }

            $user = User::factory()->create([
                'name' => $employee_info[0],
                'type' => 'employee',
            ]);
            
            $profile = new Profile();
            $employee = new Employee();

            $profile->create([
                'user_id' => $user->id,
                'first_name' => $employee_info[1],
                'last_name' => $employee_info[2],
                'date_of_birth' => Carbon::now()->subYear($employee_info[3]),
                'gender' => $employee_info[5],
            ]);

            //first remove founder title 
            //then split or partioning the manager string to array 
            //lastly assgin the first manager to manager_id in employee table
            $managers = (string) Str::replaceFirst('Founder: -> ', '', $employee_info[8]);
            $managers = explode(' -> ',$managers);
            $employee->create([
                'user_id' => $user->id,
                'manager_id' => User::where('name','like',array_pop($managers))
                                    ->where('type','like','employee')->first()->id??null,
                'salary' => $employee_info[4],
                'hired_at' => $employee_info[6],
                'job_title' => $employee_info[7],
            ]);
        }
    }
}
