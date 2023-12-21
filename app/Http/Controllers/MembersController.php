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
use App\Exports\MemberReportsExport;
use Maatwebsite\Excel\Facades\Excel;


use App\Services\UserDataService;


class MembersController extends Controller{
    public function __construct()
    {
    }
    

    public function add_member(Request $request)
    {
        // return $request;
        $date = date('Y-m-d H:i:s');
        $email = $request->input('email');
        $md5_password = md5($request->input('password'));

        $user_info=DB::table('users')
        ->where('email','=',$email)
        ->first();
        if($user_info){
            $data = array('status' => false, 'msg' => 'Email already existed, try with another email.');
            return response()->json($data);
        }
        else{
        $data = array(
            'user_name' => $request->fullname,
            'email' => $request->email,
            'gender' => $request->gender,
            'dob' => $request->dob,
            'mobile_no' => $request->phone_number,
            'location' => $request->city,
            'password' => $md5_password,
            'is_active' =>1,
            'user_type' =>3,
            'state'=>$request->state,
            'invovlement_interest'=>$request->invovlement,
            'membership_status'=>$request->membership,
            'church_id'=>$request->church_id,
            'membership_status_other'=>$request->membership_others,
            'hear_about_church'=>$request->hear_about_church,
            'hear_about_church_other'=>$request->hear_about_church_other,
            'invovlement_interest_volunteering'=>$request->volunteering_other,
            'invovlement_interest_attending'=>$request->attending_other,

            'comments'=>$request->comments
            );

            $aid= DB::table('users')->insertGetId($data);

        if ($aid) { 
            $token = Str::random(60);
            $api_token = hash('sha256', $token);
            // return $api_token;
                $update_data=DB::table('users')
                ->where('email','=',$email)
                ->where('password','=',$md5_password)
                ->update([
                    'last_login' => $date,
                    'token' => $api_token,
                ]);
                $data = array('status' => true, 'msg' => 'Member added successfully');
                return response()->json($data);

        } else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }
    }
    }
    public function get_members(REQUEST $request){
        $member_info=DB::table('users as u')
        ->join('churches as c','u.church_id','=','c.id')
        ->where('u.user_type','=',3)
        ->where('u.deleted','=',0)
        ->select('u.*','c.church_name')
        ->orderBy('created_at','DESC')
        ->get();
        
        $active_users=DB::table('users as u')
        ->join('churches as c','u.church_id','=','c.id')
        ->where('u.user_type','=',3)
        ->where('u.is_active','=',1)
        ->where('u.deleted','=',0)
        ->count();

        $inactive_users=DB::table('users as u')
        ->join('churches as c','u.church_id','=','c.id')
        ->where('u.is_active','=',0)
        ->where('u.user_type','=',3)
        ->where('u.deleted','=',0)
        ->count();

        $data = array('status' => true, 'data' => $member_info,'active_users'=>$active_users,'pending_users'=>$inactive_users);
           
        return response()->json($data);

    }
    public function get_single_member(REQUEST $request){
        $member_info=DB::table('users as u')
        ->join('churches as c','u.church_id','=','c.id')
        ->where('u.id','=',$request->id)
        ->where('u.user_type','=',3)
        ->select('u.*','c.church_name')
        ->first();

        $data = array('status' => true, 'data' => $member_info);
           
        return response()->json($data);

    }
    
    public function update_member(REQUEST $request){
        $image=null;
        if ($request->hasFile('image')) {
            // return $request->hasFile('homeTeamLogo')
            $image = $request->file('image')->store('images', 'public');
            $image = 'storage/'.$image;
        }

        $update_data=DB::table('users')
        ->where('id','=',$request->id)
        ->update([
            'avatar'=>$image,
            'user_name' => $request->user_name,
            'email' => $request->email,
            'gender' => $request->gender,
            'dob' => $request->dob,
            'mobile_no' => $request->mobile_no,
            'location' => $request->location,
            'state' => $request->state,
            'invovlement_interest' => $request->invovlement_interest,
            'membership_status' => $request->membership_status,
            'church_id' => $request->church_id,
            'membership_status_other' => $request->membership_status_other,
            'hear_about_church' => $request->hear_about_church,
            'hear_about_church_other' => $request->hear_about_church_other,
            'invovlement_interest_volunteering' => $request->invovlement_interest_volunteering,
            'invovlement_interest_attending' => $request->invovlement_interest_attending
        ]);
        if($update_data){
            $data = array('status' => true, 'msg' => 'Member details updated successfully');
            return response()->json($data);
            } 
        else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }
    }
    public function delete_member(REQUEST $request){
        $deleted_info=DB::table('users')
        ->where('id','=',$request->id)
        ->update([
            'deleted'=>1,
        ]);
     
        if($deleted_info){
            $data = array('status' => true, 'msg' => 'Member deleted successfully');
            return response()->json($data);
            } 
        else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }
    }
    public function get_members_report(Request $request)
    {
        // Assuming 'rows' is an array in the request
        $rows = $request->input('rows');
        // return $rows;
        // Additional validation if needed
        if (!is_array($rows)) {
            return response()->json(['error' => 'Invalid data format'], 400);
        }
    
        return Excel::download(new MemberReportsExport($rows), 'reports' . '.csv');
    }
    
}