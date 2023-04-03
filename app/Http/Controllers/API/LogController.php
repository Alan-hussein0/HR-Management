<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\LogResource;
use App\Models\Employee;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\BaseController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

class LogController extends BaseController
{
    public function store($data)
    {
        // dd('stop');
        $log = Log::create([
            'user_id' => Auth::id(),
            'title' => $data['title'],
            'description' => $data['description'],
        ]);
        // dd(json_encode($log));
        if (!Storage::exists('data.json')) {
            Storage::put('data.json', json_encode($log));
        }
        Storage::append('data.json', json_encode($log));

    }

    public function show($date)
    {
        $this->authorize('view',Log::class);
        
        $log = Log::where('created_at','like',$date.'%')->get();
        if ($log == null) {
            return $this->sendResponse([],'there are no logs for this day');
        } 
        
        $info = array(
            'title' => 'view logs',
            'description' => 'retrieve all logs for specific date: '.$date . '',
        );
        (new LogController)->store(data:$info);
        
        return $this->sendResponse(LogResource::collection($log), 'all logs of the date have been retrieved successfully!');
    }
}
