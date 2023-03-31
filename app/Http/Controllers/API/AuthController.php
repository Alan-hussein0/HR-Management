<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;

class AuthController extends BaseController
{
    public function register(Request $request)
    {
        $input = $request->all();
        $validator= Validator::make($input,[
            'name'=>'required',
            'type' => 'required',
            'email'=>'required|email',
            'password'=>'required',
            'c_password'=>'required|same:password'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validate error',$validator->errors());
        }

        $message=[];
        if (User::where('email',$request->email)->first()) {
            return $this->sendError('this email address is not available. choose a different address', $message);
        }


        $input['password'] = Hash::make($input['password']);
        $user = User::create($input);
        
        //create new profile for the user with dumy data
        // $this->create_profile($user->id);
        (new ProfileController)->store($user->id);
        // Profile::create([
        //         'user_id' => $user->id,
        //         'first_name' => 'first_name',
        //         'last_name' => 'last_name',
        //         'date_of_birth' => Carbon::now(),
        //     ]);

        $success['token'] = $user->createToken('HRManagementProject')->accessToken;
        $success['type'] = $user->type;
        $success['name'] = $user->name;
        $success['id']= $user->id;
        return $this->sendResponse($success,'User registered Successfully!');

    }

    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $success['token'] = $user->createToken('HRManagementProject')->accessToken;
            $success['type'] = $user->type;
            $success['name'] = $user->name;
            $success['id']= $user->id;
            return $this->sendResponse($success, 'User Login Successfully!' );
        }
        else{
            return $this->sendError('Unauthorized',['error','unauthorized']);
        }
    }

    // public function create_profile($user_id)
    // {
    //     $profile = new ProfileController();
    //     $profile->store(user_id: $user_id);
    //     return;
    // }
}
