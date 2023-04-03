<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Http\Controllers\API\BaseController;
use App\Http\Controllers\API\LogController as APILogController;
use App\Http\Controllers\LogController;
use App\Http\Resources\AllEmployeeInfoResouces;
use App\Http\Resources\EmployeeResource;
use App\Jobs\ProcessEmployeeCsv;
use App\Jobs\ProcessSendEmail;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Spatie\SimpleExcel\SimpleExcelReader;

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

        $data = array(
            'title' => 'view employees',
            'description' => 'view all employees in the system'
        );
        
        (new APILogController)->store(data:$data);

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
        
        $data = array(
            'title' => 'view specifc employee',
            'description' => 'view employee who has id: '.$employee->id.' in the system'
        );
        (new APILogController)->store(data:$data);

        return $this->sendResponse(new AllEmployeeInfoResouces($employee), 'Employee has been retrieved successfully');
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $check_change = $request->salary != $employee->salary ? true : false; 
        $this->authorize('update',$employee);

        if ($request->has('manager_id')) {
            $employee->manager_id = $request->manager_id;            
        }

        $employee->salary = $request->salary;            
        $employee->job_title = $request->job_title;            
        $employee->hired_at = $request->hired_at;            
        $employee->save();

        $data = array(
            'title' => 'update employee',
            'description' => 'update employee who has id: '.$employee->id.' in the system by HR: '.Auth::user()->name.'.',
        );
        (new APILogController)->store(data:$data);
        
        //send email
        // dd(\Illuminate\Support\Facades\App::environment());
        if ($check_change && \Illuminate\Support\Facades\App::environment('local')) {
            ProcessSendEmail::dispatch(user: $employee->user);
        }

        return $this->sendResponse(new EmployeeResource($employee), 'The employee info has been updated successfully');
    }

    public function destroy(Employee $employee)
    {
        $this->authorize('delete',$employee);

        $employee_copy = $employee;
        $employee->delete();

        $data = array(
            'title' => 'delete employee',
            'description' => 'delete employee who has id: '.$employee->id.' in the system by HR: '.Auth::user()->name.'.',
        );
        (new APILogController)->store(data:$data);

        return $this->sendResponse(new EmployeeResource($employee_copy), 'The employee has been deleted successfully!');
    }

    public function managers(Employee $employee)
    {
        $result = $this->managersSequence($employee);

        $data = array(
            'title' => 'retrieve all managers of the employee',
            'description' => 'retrieve all managers of the employee who has id: '.$employee->id.' in the system',
        );
        (new APILogController)->store(data:$data);
        
        return $this->sendResponse($result,'all managers have been retrieved');
    }

    public function managersSequence(Employee $employee,?User $user = null)
    {
        $user = $user == null ? $employee->user : $user;

        //recursion stop condition
        if ($employee->manager_id == null) {
            return ; 
        }

        $user = User::find($employee->manager_id);
        $employee = $user->employee;

        if ($user->type == 'founder' || $employee== null) {
            return 'Founder:';
        }

        //useing recursion to retrieve all manager        
        return $this->managersSequence($employee,$user) .' -> '.  $user->name ;
    }

    public function managersSalary(Employee $employee)
    {
        $result = $this->managersSalarySequence($employee);

        $data = array(
            'title' => 'retrieve all managers of the employee with thir salary',
            'description' => 'retrieve all managers of the employee with thir salary who has id: '.$employee->id.' in the system',
        );
        (new APILogController)->store(data:$data);

        return $this->sendResponse($result, 'Managers salary have been retrieved successfully');
    }

    public function managersSalarySequence(Employee $employee, ?User $user=null): array
    {
        $user = $user == null ? $employee->user : $user;

        //recursion stop condition
        if ($employee->manager_id == null) {
            return [$employee->user->name => $employee->salary.'$']; 
        }

        $user = User::find($employee->manager_id);
        $employee = $user->employee;

        //note we cannot return founder salary because as mentioned in documentation the founder not employee and salary has add to employee table
        if ($user->type == 'founder') {
            return [];
        }

        //useing recursion to retrieve all manager        
        return array_merge($this->managersSalarySequence($employee,$user),[$user->name => $user->employee->salary.'$']);        
    }

    public function exportCSV()
    {
        // $rows = [];

        // Employee::with('user')->lazyById(2000, 'id')
        //     ->each(function ($employee) use (&$rows) {
        //         $rows[] =[ 
        //             // dd($this->managersSequence($employee)),
        //             'name' => $employee->user->name,
        //             'first_name' => $employee->user->profile->first_name,
        //             'last_name' => $employee->user->profile->last_name,
        //             'age' => $employee->user->profile->age,
        //             'salary' => $employee->salary,
        //             'gender' => $employee->user->profile->gender,
        //             'hired_at' => $employee->hired_at->format('Y-m-d'),
        //             'job_title' => $employee->job_title,
        //             'managers' => $this->managersSequence($employee),
        //         ];
        //     });

            $filename = "employees.csv";
            $handle = fopen($filename, 'w+');
            fputcsv($handle, array('name','first_name','last_name', 'age', 'salary', 'gender', 'hired date', 'job title','managers'));
            // foreach($table as $row) {
                Employee::with('user')->lazyById(2000, 'id')
                ->each(function ($employee) use (&$handle) {
                    fputcsv($handle, [ 
                        // dd($this->managersSequence($employee)),
                        'name' => $employee->user->name,
                        'first_name' => $employee->user->profile->first_name,
                        'last_name' => $employee->user->profile->last_name,
                        'age' => $employee->user->profile->age,
                        'salary' => $employee->salary,
                        'gender' => $employee->user->profile->gender,
                        'hired_at' => $employee->hired_at->format('Y-m-d'),
                        'job_title' => $employee->job_title,
                        'managers' => $this->managersSequence($employee),
                        ]
                    );
                });
            // }
            fclose($handle);
            $headers = array('Content-Type' => 'text/csv');

        // $write =SimpleExcelWriter::streamDownload('empolyees.csv')
        //     ->addHeader(['name','first_name','last_name', 'age', 'salary', 'gender', 'hired date', 'job title','managers'])
        //     // ->noHeaderRow()
        //     ->addRows($rows)
        //     ->toBrowser();

        $data = array(
            'title' => 'export csv employee file',
            'description' => 'expot all employees to csv file imclude[name, fist name, last name, age, gender, salary,haired date,job title, managers]',
        );
        (new APILogController)->store(data:$data);

        // return response()->download($filename, 'employees.csv');
        // return response()->download($filename, 'employees.csv', $headers);

            return response()->download($filename, 'employees.csv', $headers);
            // return response()->json(['success'=>true,'message'=>'export csv employee file'], 200);
    }

    public function importCSV(Request $request)
    {
        if ($request->hasFile('file')) {
            
            // $request->validate([
            //     'file'=> 'required|mimes:xlsx, csv, xls'
            // ]);
            $employees_info = array_map('str_getcsv', file($request->file));

            ProcessEmployeeCsv::dispatch($employees_info);
        }

        $data = array(
            'title' => 'import csv employee file',
            'description' => 'import employees from csv file to database',
        );

        (new APILogController)->store(data:$data);

        return response()->json(['success'=>true,'message'=>'import csv file contain employees successfully'], 200);
    }

}
