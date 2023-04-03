<?php

namespace Tests\Feature\API;

use App\Http\Controllers\LogController;
use App\Models\Employee;
use App\Models\Log;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LogTest extends TestCase
{
    use RefreshDatabase;
    private User $founder;
    private User $employee;
    private User $HR;

    protected function setUp(): Void
    {
        parent::setUp();
        
        $this->artisan('passport:install');

        $this->founder = $this->createUser(type: 'founder');
        // $this->HR = $this->createEmployee(jobTitle: 'HR');
        $this->employee = $this->createUser(type: 'employee');
    }

    private function header(User $user)
    {
        return ['Accept' => 'application/json' , 'Authorization' => 'Bearer '.$this->createUserToken(user: $user)];    
    }

    private function unauthenticated_header(User $user)
    {
        return ['Accept' => 'application/json' , 'Authorization' => 'Bearer '];    
    }

    private function createUserToken(User $user)
    {
        return $user->createToken('HRManagementProject')->accessToken;
    }
    
    private function createUser(string $type)
    {
        return User::factory()->create([
            'type' => $type
        ]);
    }
    
    private function createEmployee(string $jobTitle, int $user_id = null)
    {
        return Employee::factory()->create([
            'user_id'=>$user_id,
            'job_title' => $jobTitle,
        ]);
    }

    public function test_create_new_log()
    {
        Log::factory()->create([
            'title'=>'test',
        ]);

        // (new LogController)->store()

        $this->assertDatabaseHas('logs',['title'=>'test']);
    }
}
