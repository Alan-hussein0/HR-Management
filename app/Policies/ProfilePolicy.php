<?php

namespace App\Policies;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfilePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Profile $profile)
    {
        return $user->id == $profile->user_id 
        ? Response::allow() 
        : Response::denyWithStatus(status: 403, message: 'unauthorized to make this operation');
    }   

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Profile $profile)
    {
        return $user->id == $profile->user_id 
        ? Response::allow() 
        : Response::denyWithStatus(status: 403, message: 'unauthorized to make this operation');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Profile $profile)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Profile $profile)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Profile $profile)
    {
        //
    }
}
