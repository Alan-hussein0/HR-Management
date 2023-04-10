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

    public function test_show_log_with_specific_date()
    {
        $log = Log::factory(3)->create();

        $response = $this->getJson('api/employees/'.$log->first()->created_at.'/logs',$this->header(user: $this->founder));

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'success' => true,
                    'message' => 'all logs of the date have been retrieved successfully!'
                ]
            );

        //it must have 4 because when we call the request it is add new log record, so 3 by factory and 1 when call api
        $this->assertDatabaseCount('logs',4);
    }

    public function test_show_log_with_specific_date_unauthorized_return_error()
    {
        $log = Log::factory(3)->create();

        $response = $this->getJson('api/employees/'.$log->first()->created_at.'/logs',$this->header(user: $this->createEmployee(jobTitle: 'it',user_id: $this->createUser(type: 'employee')->id)->user));

        $response->assertStatus(403)
                ->assertJsonFragment([
                    'message' => 'you not authrized for this process'
                ]
            );

        //it must have 3 because when we call the request it will fail so now log record has been added.
        $this->assertDatabaseCount('logs',3);
    }
}
