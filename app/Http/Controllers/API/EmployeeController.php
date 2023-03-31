<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Http\Controllers\API\BaseController;
use App\Http\Resources\AllEmployeeInfoResouces;
use App\Http\Resources\EmployeeResource;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeController extends BaseController
{

    public function index(Request $request)
    {
        $sort_name = null;

        if ($request->has('sort_name')) {
            $sort_name = $request->sort_name;
            //user point to user() in employee model
            //this query will return all employee in best preformance 
            $employee = Employee::with(['user' => fn($query) => $query->where('name', 'like', '%'.$sort_name.'%')])
                        ->whereHas('user', fn ($query) => 
                        $query->where('name', 'like', '%'.$sort_name.'%')
                        )
                        ->cursorPaginate(15);
            return $this->sendResponse(EmployeeResource::collection($employee), 'The employee have been retrieved successfully');
        }

        $employee = Employee::orderBy('created_at','desc')->cursorPaginate(15);

        return $this->sendResponse(EmployeeResource::collection($employee), 'The employee have been retrieved successfully');
    }

    public function store($user_id)
    {
        return Employee::create([
            'user_id' => $user_id,
            // 'manager_id' =>  $request->manager_id,
            'salary' => 0,
            'hired_at' => Carbon::now(),
            'job_title' => 'job_title',
        ]);
    }

    public function show(Employee $employee)
    {
        return $this->sendResponse(new AllEmployeeInfoResouces($employee), 'Employee has been retrieved successfully');
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $this->authorize('update',$employee);

        if ($request->has('manager_id')) {
            $employee->manager_id = $request->manager_id;            
        }
        $employee->salary = $request->salary;            
        $employee->job_title = $request->job_title;            
        $employee->hired_at = $request->hired_at;            
        $employee->save();

        return $this->sendResponse(new EmployeeResource($employee), 'The employee info has been updated successfully');
    }

    public function destroy(Employee $employee)
    {
        $this->authorize('delete');

        $employee_copy = $employee;
        $employee->delete();

        return $this->sendResponse(new EmployeeResource($employee_copy), 'The employee has been deleted successfully!');
    }
}
