<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends BaseController
{
    public function show(User $user)
    {
        $this->authorize('view',$user->profile);
    
        $profile = $user->profile;
        
        $data = array(
            'title' => 'view profile',
            'description' => 'user view the profile',
        );
        (new LogController)->store(data:$data);

        return $this->sendResponse(new ProfileResource($profile), 'the profile has been retrieved successfully');
    }

    public function store($user_id)
    {
        return Profile::create([
                'user_id' => $user_id,
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'date_of_birth' => Carbon::now(),
            ]);
    } 
    
    public function update(UpdateProfileRequest $request, Profile $profile)
    {
        //validate the input request
        // $validated = $request->validated();

        //check if the user authrize 
        $this->authorize('update', $profile);

        $profile->first_name = $request->first_name;
        $profile->last_name = $request->last_name;
        $profile->date_of_birth = $request->date_of_birth;
        $profile->gender = $request->gender;

        if ($request->phone) {
            $profile->phone = $request->phone;
        }

        $profile->save();

        $data = array(
            'title' => 'update profile',
            'description' => 'The user updating his profile',
        );
        (new LogController)->store(data:$data);

        return $this->sendResponse(new ProfileResource($profile), 'The profile has been updated successfully');
    }
}
