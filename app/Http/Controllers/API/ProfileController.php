<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProfileController extends BaseController
{
    public function show(User $user)
    {
        $profile = $user->profile();
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
        $this->authorize('update',Profile::class);

        $profile->first_name = $request->first_name;
        $profile->last_name = $request->last_name;
        $profile->date_of_birth = $request->date_of_birth;
        $profile->gender = $request->gender;

        if ($request->phone) {
            $profile->phone = $request->phone;
        }

        $profile->save();

        return $this->sendResponse(new ProfileResource($profile), 'The profile has been updated successfully');
    }
}
