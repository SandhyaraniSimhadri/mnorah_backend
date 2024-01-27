<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Imports\EventImport;
use Maatwebsite\Excel\Facades\Excel;
use App;
use Response;
use App\Services\UserDataService;
use App\Exports\EventsReportExport;


class EventController extends Controller{
    public function __construct()
    {
    }
    

    public function add_event(Request $request)
    {

        $date = date('Y-m-d H:i:s');
  
        $data = array(
            'church_id' => $request->church_id,
            'event_name' => $request->event_name,
            'event_type' => $request->event_type,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'venue' => $request->venue,
            'contact_person'=>$request->contact_person,
            'frequency' => $request->frequency,
            'event_description' => $request->event_description,
            'agenda' => $request->agenda,
            'reg_info' => $request->reg_info,
            'speakers' => $request->speakers,
            'special_req' => $request->special_req,
            'dress_code'=>$request->dress_code,
            'additional_info'=>$request->additional_info,
            );

            $aid= DB::table('events')->insertGetId($data);

        if ($aid) { 
                $data = array('status' => true, 'msg' => 'event added successfully!');
                return response()->json($data);

        } else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }

    }
    public function get_events(REQUEST $request){
        $query = DB::table('events as e')
            ->join('churches as c', 'c.id', '=', 'e.church_id')
            ->where('e.deleted', '=', 0)
            ->select('e.*', 'c.church_name', DB::raw('e.image as avatar'))
            ->orderBy('e.created_at', 'DESC');
    
        if ($request['logged_user_type'] == 1) {
            $event_info = $query->get();
        } else if ($request['logged_user_type'] == 2) {
            $event_info = $query->where('e.church_id', '=', $request['logged_church_id'])->get();
        }
    
        return response()->json(['status' => true, 'data' => $event_info]);
    }

    public function get_single_event(REQUEST $request){
        $event_info=DB::table('events as e')
        ->join('churches as c','c.id', '=','e.church_id')
        ->where('e.id','=',$request->id)
        ->select('e.*','c.church_name',DB::raW('e.image as avatar'))
        ->first();
        return response()->json(['status' => true, 'data' => $event_info]);
    }
    
    public function update_event(REQUEST $request){
        $image=null;
        if ($request->hasFile('image')) {
            // return $request->hasFile('homeTeamLogo')
            $image = $request->file('image')->store('images', 'public');
            $image = 'storage/'.$image;
        }
        $update_data=DB::table('events')
        ->where('id','=',$request->id)
        ->update([
            'church_id' => $request->church_id,
            'event_name' => $request->event_name,
            'event_type' => $request->event_type,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'venue' => $request->venue,
            'contact_person'=>$request->contact_person,
            'frequency' => $request->frequency,
            'event_description' => $request->event_description,
            'agenda' => $request->agenda,
            'reg_info' => $request->reg_info,
            'speakers' => $request->speakers,
            'special_req' => $request->special_req,
            'dress_code'=>$request->dress_code,
            'additional_info'=>$request->additional_info,
            'image'=>$image
        ]);
        if($update_data){
            $data = array('status' => true, 'msg' => 'Event details updated successfully');
            return response()->json($data);
            } 
        else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }
    }

    public function delete_event(REQUEST $request){
        $deleted_info=DB::table('events')
        ->where('id','=',$request->id)
        ->update([
            'deleted'=>1,
        ]);
    
        if($deleted_info){
            $data = array('status' => true, 'msg' => 'Event deleted successfully');
            return response()->json($data);
            } 
        else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }
    }
    public function event_file_import(Request $request) 
    {
        $collection = Excel::toCollection(new EventImport, $request->file('file'))->toArray();
        $data1 = $collection[0];
        // return $data1;
        $date = date('Y-m-d H:i:s');
        $count=0;

        foreach ($data1 as $event) {
        
            $church_info = DB::table('churches as c')
            ->where('c.is_active', '=', 1)
            ->where('c.deleted', '=', 0)
            ->where('c.church_name','=',$event['church_name']) 
            ->first();
   
            if($church_info && $event['event_name'] && $event['event_type'] && $event['event_date'] && $event['event_time']
            && $event['venue']   && $event['contact_person'])
            {
                // return true;
            $count= $count+1;
        
                $data = array(
                    'church_id' => $church_info->id,
                    'event_name' => $event['event_name'],
                    'event_type' => $event['event_type'],
                    'event_date' => $event['event_date'],
                    'event_time' => $event['event_time'],
                    'venue' => $event['venue'],
                    'speakers' => $event['speakers'],
                    'contact_person'=>$event['contact_person'],
                    'frequency' => $event['frequency'],
                    'event_description' => $event['event_description'],
                    'agenda' => $event['agenda'],
                    'reg_info' => $event['registration_information'],
                    'dress_code'=>$event['dress_code'],
                    'special_req' => $event['special_requirements'],
                    'additional_info'=>$event['additional_information'],
                    );
                
                    $aid= DB::table('events')->insertGetId($data);}
            else{
                    continue;
                }
        }
        return json_encode(array('status' => true, 'msg' => 'Events data uploaded successfully','count'=>$count));
            
    }
    public function download_event_sample()
    {
        $filepath = public_path('samples/event_sample.csv');
        // return $filepath;
        return Response::download($filepath);
    }

    public function get_events_report(Request $request)
    {
        // Assuming 'rows' is an array in the request
        $rows = $request->input('rows');
        // return $rows;
        // Additional validation if needed
        if (!is_array($rows)) {
            return response()->json(['error' => 'Invalid data format'], 400);
        }
    
        return Excel::download(new EventsReportExport($rows), 'reports' . '.csv');
    }
}