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


class PrayerRequestController extends Controller{
    public function __construct()
    {
    }
    

    public function add_prayer_request(Request $request)
    {
        $image=null;
        if ($request->hasFile('image')) {
            // return $request->hasFile('homeTeamLogo')
            $image = $request->file('image')->store('images', 'public');
            $image = 'storage/'.$image;
        }
        $date = date('Y-m-d H:i:s');
        $data = array(
            'church_id' => $request->church_id,
            'member_id' => $request->member_id,
            'prayer_request' => $request->prayer_request,
            'prayer_request_other' => $request->prayer_request_other
            );

            $aid= DB::table('prayer_requests')->insertGetId($data);

        if ($aid) { 
                $data = array('status' => true, 'msg' => 'Prayer requests added successfully');
                return response()->json($data);

        } else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }

    }
    public function get_prayer_requests(REQUEST $request){
        $prayer_request_info=DB::table('prayer_requests as p')
        ->join('churches as c','p.church_id', '=','c.id')
        ->join('users as u','p.member_id', '=','u.id')
        ->where('p.deleted','=',0)
        ->where('c.deleted','=',0)
        ->where('u.deleted','=',0)
        ->select('p.*','c.church_name','u.user_name',DB::raW('p.image as avatar'))
        ->orderBy('p.created_at','DESC')
        ->get();
        $data = array('status' => true, 'data' => $prayer_request_info);
        return response()->json($data);
           
    }

    public function get_single_prayer_request(REQUEST $request){
        $prayer_request_info=DB::table('prayer_requests as p')
        ->join('churches as c','p.church_id', '=','c.id')
        ->join('users as u','p.member_id', '=','u.id')
        ->where('p.id','=',$request->id)
        ->where('p.deleted','=',0)
        ->where('c.deleted','=',0)
        ->where('u.deleted','=',0)
        ->select('p.*','c.church_name','u.user_name',DB::raw('p.image as avatar'))
        ->first();
        $data = array('status' => true, 'data' => $prayer_request_info);
        return response()->json($data);
    }
    
    public function update_prayer_request(REQUEST $request){
        $image=null;
        if ($request->hasFile('image')) {
            // return $request->hasFile('homeTeamLogo')
            $image = $request->file('image')->store('images', 'public');
            $image = 'storage/'.$image;
        }
        $update_data=DB::table('prayer_requests')
        ->where('id','=',$request->id)
        ->update([
            'church_id' => $request->church_id,
            'member_id' => $request->member_id,
            'prayer_request' => $request->prayer_request,
            'prayer_request_other' => $request->prayer_request_other
        ]);
        if($update_data){
            $data = array('status' => true, 'msg' => 'Prayer request details updated successfully');
            return response()->json($data);
            } 
        else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }
}
public function delete_prayer_request(REQUEST $request){
    $deleted_info=DB::table('prayer_requests')
    ->where('id','=',$request->id)
    ->update([
        'deleted'=>1,
    ]);
 
    if($deleted_info){
        $data = array('status' => true, 'msg' => 'Testimony deleted successfully');
        return response()->json($data);
        } 
    else {
        // return true;
        $data = array('status' => false, 'msg' => 'Failed');
        return response()->json($data);
    }
}
public function get_church_members(REQUEST $request){
    $members_info=DB::table('users')
    ->where('church_id','=',$request->church_id)
    ->where('deleted','=',0)
    ->where('user_type','=',3)
    ->select('*')
    ->get();

    $data = array('status' => true, 'data' => $members_info);
       
    return response()->json($data);

}


}