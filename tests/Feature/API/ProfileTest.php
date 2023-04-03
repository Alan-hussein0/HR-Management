<?php

namespace Tests\Feature\API;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProfileController;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;
    private User $founder;
    private User $employee;

    protected function setUp(): Void
    {
        parent::setUp();
        
        $this->artisan('passport:install');

        $this->founder = $this->createUser(type: 'founder');
        $this->employee = $this->createUser(type: 'employee');
    }

    private function header(User $user)
    {
        return ['Accept' => 'application/json' , 'Authorization' => 'Bearer '.$this->createUserToken(user: $user)];    
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

    public function test_view_profile_successfully()
    {
        $user = $this->employee;
        Profile::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'date_of_birth' => Carbon::now(),
            'gender' => 0,
            'phone' => null
        ]);

        $response = $this->getJson('/api/user/profile/'.$user->id, $this->header(user: $user));

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'user_id' => $user->id,
                        'first_name' => 'first_name',
                        'last_name' => 'last_name',
                        'date_of_birth' => Carbon::now(),
                        'gender' => 0,
                        'phone' => null
                    ],
                    'message' => 'the profile has been retrieved successfully'
                ]);

    }

    public function test_view_profile_anuthorize_return_error()
    {
        $user = $this->employee;
        Profile::factory()->create([
            'user_id' => 4 != $user->id ?? 5 ,
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'date_of_birth' => Carbon::now(),
            'gender' => 0,
            'phone' => null
        ]);

        $response = $this->getJson('/api/user/profile/'.$user->id, $this->header(user: $user));

        $response->assertStatus(403)
                ->assertJson([
                    'message' => 'This action is unauthorized.'
                ]);
    }

    public function test_update_profile_successfully()
    {
        $user = $this->employee;
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
        ]);
        
        //gender 0 for male and 1 for female
        $data = [
            'first_name' => 'ahmed',
            'last_name' => 'hasen',
            'date_of_birth' => Carbon::createFromDate(1995,3,12),
            'gender' => 0,
        ];

        $response = $this->patchJson('/api/user/profile/'.$profile->id, $data, $this->header(user: $user));

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'success' => true,
                    // 'data' => [
                        'user_id' => $user->id,
                        'first_name' => 'ahmed',
                        'last_name' => 'hasen',
                        'gender' => 0,
                    // ],
                    'message' => 'The profile has been updated successfully'
                ]);
    }

    public function test_update_profile_invalid_data_return_error_successfully()
    {
        $user = $this->employee;
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
        ]);
        
        //gender 0 for male and 1 for female
        $data = [
            'first_name' => '',
            'last_name' => 'hasen',
            'date_of_birth' => Carbon::createFromDate(1995,3,12),
            'gender' => 0,
        ];

        $response = $this->patchJson('/api/user/profile/'.$profile->id, $data, $this->header(user: $user));

        $response->assertStatus(422)
                ->assertJson([
                    'message' => 'The first name field is required.',
                    'errors' => [
                        'first_name' => [
                            'The first name field is required.'
                        ]
                    ]
                ]);
    }

    public function test_update_profile_unauthorized_return_error_successfully()
    {
        $user = $this->employee;
        $profile = Profile::factory()->create([
            'user_id' => 3 != $user->id ?? 4,
        ]);
        
        //gender 0 for male and 1 for female
        $data = [
            'first_name' => 'ahmed',
            'last_name' => 'hasen',
            'date_of_birth' => Carbon::createFromDate(1995,3,12),
            'gender' => 0,
        ];

        $response = $this->patchJson('/api/user/profile/'.$profile->id, $data, $this->header(user: $user));

        $response->assertStatus(403)
                ->assertJsonFragment([
                    'message' => 'unauthorized to make this operation'
                ]);
    }
}
