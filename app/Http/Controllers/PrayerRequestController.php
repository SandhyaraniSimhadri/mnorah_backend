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
use Response;
use Maatwebsite\Excel\Facades\Excel;

use App\Imports\PrayerRequestImport;

use App\Exports\PrayerRequestsReportExport;

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
            'description' => $request->description
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


        $query=DB::table('prayer_requests as p')
        ->join('churches as c','p.church_id', '=','c.id')
        ->join('users as u','p.member_id', '=','u.id')
        ->where('p.deleted','=',0)
        ->where('c.deleted','=',0)
        ->where('u.deleted','=',0)
        ->select('p.*','c.church_name','u.user_name',DB::raW('p.image as avatar'))
        ->orderBy('p.created_at','DESC');

        if ($request['logged_user_type'] == 1) {
            $requests_info = $query->get();
        } else if ($request['logged_user_type'] == 2) {
            $requests_info = $query->where('p.church_id', '=', $request['logged_church_id'])->get();
        }

        return response()->json(['status' => true, 'data' => $requests_info]);
           
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
            'description' => $request->description
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

    public function prayer_request_file_import(Request $request) 
    {
        $collection = Excel::toCollection(new PrayerRequestImport, $request->file('file'))->toArray();
        $data1 = $collection[0];
        // return $data1;
        $date = date('Y-m-d H:i:s');
        $count=0;

        foreach ($data1 as $prayer_request) {
            $prayer_request_val=null;
            $church_info = DB::table('churches as c')
            ->where('c.is_active', '=', 1)
            ->where('c.deleted', '=', 0)
            ->where('c.church_name','=',$prayer_request['church_name']) 
            ->first();
            if( $prayer_request['prayer_request']!= 'Family and Relationships' &&  
            $prayer_request['prayer_request']!= 'Financial stability' && 
            $prayer_request['prayer_request']!= 'Advertisement' &&
            $prayer_request['prayer_request']!= 'Work' &&
            $prayer_request['prayer_request']!= 'Grief' &&
            $prayer_request['prayer_request']!= 'Spiritual Growth' &&
            $prayer_request['prayer_request']!= 'Personal Struggles' &&
            $prayer_request['prayer_request']!= 'Prayers for peace' &&
            $prayer_request['prayer_request']!= 'Global Issues' &&
            $prayer_request['prayer_request']!= 'Guidance and Decision-Making' && 
            $prayer_request['prayer_request']!= 'Mission and Outreach'){
               
                $prayer_request_val =  'Other';
                 
            }else{
                $prayer_request_val = $prayer_request['prayer_request'];
            }
            // return $prayer_request;
            if( $church_info && $prayer_request['member'] && $prayer_request && $prayer_request['description'])
            {
                $count= $count+1;
        
                $data = array(
                    'church_id' => $church_info->id,
                    'member_id' => $prayer_request['member'],
                    'prayer_request' => $prayer_request_val,
                    'description' => $prayer_request['description']
                    );
                
                $aid= DB::table('prayer_requests')->insertGetId($data);}
            else{
                continue;
            }
        }
                return json_encode(array('status' => true, 'msg' => 'Prayer requests data uploaded successfully','count'=>$count));
            
    }
    public function download_prayer_request_sample()
    {
        $filepath = public_path('samples/prayer_request_sample.csv');
        return Response::download($filepath);
    }
    public function get_prayer_requests_report(Request $request)
    {
        // Assuming 'rows' is an array in the request
        $rows = $request->input('rows');
        // return $rows;
        // Additional validation if needed
        if (!is_array($rows)) {
            return response()->json(['error' => 'Invalid data format'], 400);
        }
    
        return Excel::download(new PrayerRequestsReportExport($rows), 'reports' . '.csv');
    }
}