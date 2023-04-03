<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AllEmployeeInfoResouces extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'name' => $this->user->name,
            'first_name' => $this->user->profile->first_name,
            'last_name' => $this->user->profile->last_name,
            'age' => $this->user->profile->age,
            'salary' => $this->salary,
            'gender' => $this->user->profile->gender,
            'hired_at' => $this->hired_at->format('Y-m-d h:m:s'),
            'job_title' => $this->job_title,
        ];
    }
}
