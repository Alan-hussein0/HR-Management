<?php

namespace Tests\Feature\API;

use App\Models\Employee;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;
    private User $fonder;
    private User $employee;
    private User $HR;

    protected function setUp(): Void
    {
        parent::setUp();
        
        $this->artisan('passport:install');

        $this->fonder = $this->createUser(type: 'fonder');
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

    public function test_view_all_employees_successfully()
    {
        $user = $this->employee;
        Employee::factory(100)->create();

        $response = $this->getJson('/api/employees/search',$this->header(user: $user));
        // dd($response->json());
        $response->assertStatus(200)
                ->assertJsonFragment([
                    'success' => true,
                    'message' => 'The employee have been retrieved successfully'
                ])
                ->assertJsonCount(15,'data');
    }

    public function test_view_all_employees_whose_names_start_with_al_successfully()
    {
        $user = $this->employee;
        Employee::factory(100)->create();

        $response = $this->getJson('/api/employees/search?sort_name=al',$this->header(user: $user));

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'success' => true,
                    'message' => 'The employee have been retrieved successfully'
                ]);
    }

    public function test_view_all_employees_unauthenticated_return_error_successfully()
    {
        $user = $this->employee;
        Employee::factory(10)->create();

        $response = $this->getJson('/api/employees/search',$this->unauthenticated_header(user: $user));

        $response->assertStatus(401)
                ->assertJsonFragment([
                    'message' => 'Unauthenticated.'
                ]);
    }

    public function test_show_specific_employee_with_info_successfully()
    {
        $user = $this->employee;
        Profile::factory()->create([
            'user_id'=> $user->id,
        ]);
        $employee = Employee::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->getJson('/api/employees/'.$employee->id, $this->header(user: $user));
        // dd($response->json());
        $response->assertStatus(200)
                    ->assertJsonFragment([
                        'success' => true,
                        'name' => $user->name,
                        'salary' => $employee->salary,
                        'age' => $user->profile->age,
                        'message' => 'Employee has been retrieved successfully'
                    ]);
    }

    public function test_update_employee_successfully()
    {
        $HR = $this->createEmployee(jobTitle: 'HR',user_id: $this->employee->id);
        $employee = Employee::factory()->create();
        $data = [
            'manager_id' => $this->employee->employee->id,
            'salary' => 1000,
            'hired_at' => Carbon::yesterday(),
            'job_title' => 'IT',
        ];

        $response = $this->patchJson('/api/employees/'.$employee->id, $data, $this->header(user: $HR->user));

        $response->assertStatus(200)
                    ->assertJsonFragment([
                        'success' => true,
                        'salary' => 1000,
                        'job_title' => 'IT',
                        "message" => 'The employee info has been updated successfully'
                    ]);

    }
}
