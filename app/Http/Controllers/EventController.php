<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App;



use App\Services\UserDataService;


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
        $event_info=DB::table('events as e')
        ->join('churches as c','c.id', '=','e.church_id')
        ->where('e.deleted','=',0)
        ->select('e.*','c.church_name',DB::raW('e.image as avatar'))
        ->orderBy('e.created_at','DESC')
        ->get();
        $data = array('status' => true, 'data' => $event_info);
        return response()->json($data);
           
    }

    public function get_single_event(REQUEST $request){
        $event_info=DB::table('events as e')
        ->join('churches as c','c.id', '=','e.church_id')
        ->where('e.id','=',$request->id)
        ->select('e.*','c.church_name',DB::raW('e.image as avatar'))
        ->first();
        $data = array('status' => true, 'data' => $event_info);        
        return response()->json($data);
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

}