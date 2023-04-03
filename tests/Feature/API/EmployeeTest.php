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

    public function test_view_all_employees_successfully()
    {
        $user = $this->employee;
        Employee::factory(100)->create();

        $response = $this->getJson('/api/employees/search',$this->header(user: $user));

        //test log info to db also
        $this->assertDatabaseCount('logs',1)
                ->assertDatabaseHas('logs',['title'=>'view employees']);
        
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

        //test log info to db also
        $this->assertDatabaseCount('logs',1)
                ->assertDatabaseHas('logs',['description'=>'view employee who has id: '.$employee->id.' in the system']);
        
        $response->assertStatus(200)
                ->assertJsonFragment([
                    'success' => true,
                    'name' => $user->name,
                    'salary' => $employee->salary,
                    'age' => $user->profile->age,
                    'message' => 'Employee has been retrieved successfully'
                ]);
    }

    public function test_show_undefine_employee_return_error_successfully()
    {
        $user = $this->employee;
        Profile::factory()->create([
            'user_id'=> $user->id,
        ]);
        $employee = Employee::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->getJson('/api/employees/22', $this->header(user: $user));
        // dd($response->json());
        $response->assertStatus(404)
                    ->assertJsonFragment([
                        'message' => 'No query results for model [App\\Models\\Employee] 22'
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

        //test log info to db also
        $this->assertDatabaseCount('logs',1)
        ->assertDatabaseHas('logs',['description'=>'update employee who has id: '.$employee->id.' in the system by HR: '.$HR->user->name.'.']);

        $response->assertStatus(200)
                    ->assertJsonFragment([
                        'success' => true,
                        'salary' => 1000,
                        'job_title' => 'IT',
                        "message" => 'The employee info has been updated successfully'
                    ]);
    }

    public function test_update_employee_with_invalid_input_return_error_successfully()
    {
        $HR = $this->createEmployee(jobTitle: 'HR',user_id: $this->employee->id);
        $employee = Employee::factory()->create();
        $data = [
            'manager_id' => $this->employee->employee->id,
            'salary' => 1000,
            'hired_at' => Carbon::yesterday(),
            'job_title' => '',
        ];

        $response = $this->patchJson('/api/employees/'.$employee->id, $data, $this->header(user: $HR->user));

        $response->assertStatus(422)
                    ->assertJsonFragment([
                        "message" => 'The job title field is required.'
                    ]);
    }

    public function test_update_employee_unauthorized_return_error_successfully()
    {
        $HR = $this->createEmployee(jobTitle: 'IT',user_id: $this->employee->id);
        $employee = Employee::factory()->create();
        $data = [
            'manager_id' => $this->employee->employee->id,
            'salary' => 1000,
            'hired_at' => Carbon::yesterday(),
            'job_title' => 'IT',
        ];

        $response = $this->patchJson('/api/employees/'.$employee->id, $data, $this->header(user: $HR->user));

        $response->assertStatus(403)
                    ->assertJsonFragment([
                        'message' => 'you not authrized for this process'
                    ]);
    }

    public function test_update_employee_unauthenticated_reutrn_error_successfully()
    {
        $HR = $this->createEmployee(jobTitle: 'HR',user_id: $this->employee->id);
        $employee = Employee::factory()->create();
        $data = [
            'manager_id' => $this->employee->employee->id,
            'salary' => 100,
            'hired_at' => Carbon::yesterday(),
            'job_title' => 'IT',
        ];

        $response = $this->patchJson('/api/employees/'.$employee->id, $data, $this->unauthenticated_header(user: $HR->user));

        $response->assertStatus(401)
        ->assertJsonFragment([
            'message' => 'Unauthenticated.'
        ]);
    }

    public function test_delete_employee_successfully()
    {
        $employee = $this->createEmployee(jobTitle: 'IT',user_id: $this->createUser('employee')->id);
        $HR = $this->createEmployee(jobTitle: 'HR',user_id: $this->employee->id);

        $response = $this->deleteJson('/api/employees/'.$employee->id,[],$this->header(user: $HR->user));

        //test log info to db also
        $this->assertDatabaseCount('logs',1)
        ->assertDatabaseHas('logs',['description'=>'delete employee who has id: '.$employee->id.' in the system by HR: '.$HR->user->name.'.']);

        $response->assertStatus(200)
                    ->assertJsonFragment([
                        'message' => 'The employee has been deleted successfully!',
                    ]);
        $this->assertDatabaseCount('employees',1);
        $this->assertDatabaseMissing('employees',['id'=>$employee->id]);

    }

    public function test_delete_employee_unauthenticated_return_error_successfully()
    {
        $employee = $this->createEmployee(jobTitle: 'IT',user_id: $this->employee->id);
        $HR = $this->createEmployee(jobTitle: 'HR',user_id: $this->employee->id);

        $response = $this->deleteJson('/api/employees/'.$employee->id,[],$this->unauthenticated_header(user: $HR->user));

        $response->assertStatus(401)
                    ->assertJsonFragment([
                        'message' => 'Unauthenticated.',
                    ]);

    }

    public function test_delete_employee_unauthorized_return_error_successfully()
    {
        $employee = $this->createEmployee(jobTitle: 'security',user_id: $this->createUser('employee')->id);
        $employee_IT = $this->createEmployee(jobTitle: 'IT',user_id: $this->createUser('employee')->id);
        // $HR = $this->createEmployee(jobTitle: 'HR',user_id: $this->employee->id);

        $response = $this->deleteJson('/api/employees/'.$employee->id,[],$this->header(user: $employee_IT->user));

        $response->assertStatus(403)
                    ->assertJsonFragment([
                        'message' => 'you not authrized for this process',
                    ]);

    }

    public function test_HR_employee_delete_himself_return_error_successfully()
    {
        // $employee = $this->createEmployee(jobTitle: 'security',user_id: $this->createUser('employee')->id);
        // $employee_IT = $this->createEmployee(jobTitle: 'IT',user_id: $this->createUser('employee')->id);
        $HR = $this->createEmployee(jobTitle: 'HR',user_id: $this->employee->id);

        $response = $this->deleteJson('/api/employees/'.$HR->id,[],$this->header(user: $HR->user));

        $response->assertStatus(403)
                    ->assertJsonFragment([
                        'message' => 'you not authrized for this process',
                    ]);

    }

    public function test_retrieve_all_managers_of_employee_successfully()
    {
        $user = array();

        for ($id=1; $id <= 5; $id++) { 
            $user[$id] =User::factory()->create([
                'type'=>'employee',
            ]);            
        }
        
        $founder = $this->founder;

        for ($id=1; $id <= 4; $id++) {
            if ($id == 4) {
                Employee::factory()->create([
                    'user_id'=>$user[$id]->id,
                    'manager_id' => $founder->id,
                ]);
                continue;    
            } 
            Employee::factory()->create([
                'user_id'=>$user[$id]->id,
                'manager_id' => $user[$id+1]->id,
            ]);
        }
        
        $employee = Employee::where('user_id',$user[1]->id)->first();

        $response = $this->getJson('/api/employees/'.$employee->id.'/managers',$this->header(user:$employee->user));
        
        //test log info to database also
        $this->assertDatabaseCount('logs',1)
        ->assertDatabaseHas('logs',['description'=>'retrieve all managers of the employee who has id: '.$employee->id.' in the system']);        

        $response->assertStatus(200)
                    ->assertJsonFragment([
                        'success' => true,
                        'message' => 'all managers have been retrieved'
                    ]);
    }

    public function test_retrieve_all_managers_salary_of_employee_successfully()
    {
        $user = array();

        for ($id=1; $id <= 5; $id++) { 
            $user[$id] =User::factory()->create([
                'type'=>'employee',
            ]);            
        }
        
        $founder = $this->founder;

        for ($id=1; $id <= 4; $id++) {
            if ($id == 4) {
                Employee::factory()->create([
                    'user_id'=>$user[$id]->id,
                    'manager_id' => $founder->id,
                ]);
                continue;    
            } 
            Employee::factory()->create([
                'user_id'=>$user[$id]->id,
                'manager_id' => $user[$id+1]->id,
            ]);
        }
        
        $employee = Employee::where('user_id',$user[1]->id)->first();

        $response = $this->getJson('/api/employees/'.$employee->id.'/managers-salary',$this->header(user:$employee->user));

        //test log info to database also
        $this->assertDatabaseCount('logs',1)
        ->assertDatabaseHas('logs',['description'=>'retrieve all managers of the employee with thir salary who has id: '.$employee->id.' in the system']);        

        $response->assertStatus(200)
                    ->assertJsonFragment([
                        'success' => true,
                        'message' => 'Managers salary have been retrieved successfully'
                    ]);
    }
}
