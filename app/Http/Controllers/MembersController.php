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
use App\Exports\MembersReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Response;
use App\Imports\MembersImport;



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
        $query=DB::table('users as u')
        ->leftJoin('churches as c','u.church_id', '=','c.id')
        ->where('u.deleted','=',0)
        ->where('u.user_type','=',3)
        ->select('u.*','c.church_name',DB::raW('u.avatar as avatar'),DB::raW('u.id as member_id'))
        ->orderBy('u.created_at','DESC');

        if ($request['logged_user_type'] == 1) {
            $member_info = $query->get();
        } else if ($request['logged_user_type'] == 2) {
            $member_info = $query->where('u.church_id', '=', $request['logged_church_id'])->get();
        }
      

        $query=DB::table('users as u')
        ->leftJoin('churches as c','u.church_id', '=','c.id')
        ->where('u.deleted','=',0)
        ->where('u.user_type','=',3)
        ->where('u.is_active','=',1)
        ->select('u.*','c.church_name',DB::raW('u.image as avatar'))
        ->orderBy('u.created_at','DESC');

        if ($request['logged_user_type'] == 1) {
            $active_users = $query->count(); // Count for the first condition
        } else if ($request['logged_user_type'] == 2) {
            $active_users = $query->where('u.church_id', '=', $request['logged_church_id'])->count(); // Count for the second condition
        }

        $query=DB::table('users as u')
        ->leftJoin('churches as c','u.church_id', '=','c.id')
        ->where('u.deleted','=',0)
        ->where('u.user_type','=',3)
        ->where('u.is_active','=',1)
        ->select('u.*','c.church_name',DB::raW('u.image as avatar'))
        ->orderBy('u.created_at','DESC');

        if ($request['logged_user_type'] == 1) {
         
            $inactive_users = $query->count(); // Count for the first condition
        } else if ($request['logged_user_type'] == 2) {
            $inactive_users = $query->where('u.church_id', '=', $request['logged_church_id'])->count(); // Count for the second condition
        }
        $data = array('status' => true, 'data' => $member_info,'active_users'=>$active_users,'pending_users'=>$inactive_users);
           
        return response()->json($data);

    }
    public function get_single_member(REQUEST $request){
        $member_info=DB::table('users as u')
        ->leftJoin('churches as c','u.church_id','=','c.id')
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
            'comments' => $request->comments,
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
    
        return Excel::download(new MembersReportExport($rows), 'reports' . '.csv');
    }
    public function download_member_sample()
    {
        $filepath = public_path('samples/member_sample.csv');
        // return $filepath;
        return Response::download($filepath);
    }
    public function member_file_import(Request $request) 
    {
        $collection = Excel::toCollection(new citiesImport, $request->file('file'))->toArray();
        $data1 = $collection[0];
        // return $data1;
        $date = date('Y-m-d H:i:s');
        $count=0;

        foreach ($data1 as $member) {
            $membership_status=null;
            $membership_status_other=null;
            $hear_about=null;
            $hear_about_other=null;
            $involvement_and_interest=null;
            $invovlement_interest_volunteering=null;
            $church_info = DB::table('churches as c')
            ->where('c.is_active', '=', 1)
            ->where('c.deleted', '=', 0)
            ->where('c.church_name','=',$member['church_name']) 
            ->first();

            if($member['membership_status']!= 'New Member' &&  
            $member['membership_status']!= 'Returning Member' && 
            $member['membership_status']!= 'Visitor'){
                $membership_status = 'Other'; 
                $membership_status_other = $member['membership_status'];
                 
            }else{
                $membership_status = $member['membership_status'];
            }


            if($member['how_did_you_hear_about_our_church']!= 'Word of Mouth' &&  
            $member['how_did_you_hear_about_our_church']!= 'Website' && 
            $member['how_did_you_hear_about_our_church']!= 'Social Media' && 
            $member['how_did_you_hear_about_our_church']!= 'Invitation'){
                $hear_about = 'Other'; 
                $hear_about_other = $member['how_did_you_hear_about_our_church'];
                 
            }else{
                $hear_about_other = $member['how_did_you_hear_about_our_church'];
            }


            if($member['involvement_and_interests']!= 'Ushering' &&  
            $member['involvement_and_interests']!= "Children's Ministry" && 
            $member['involvement_and_interests']!= 'Music Ministry' && 
            $member['involvement_and_interests']!= 'Sunday School'  && 
            $member['involvement_and_interests']!= 'Sunday Service' && $member['involvement_and_interests']!= 'Bible Study' &&  
            $member['involvement_and_interests']!= "Fellowship Events" && 
            $member['involvement_and_interests']!= 'Community Outreach'){
                $involvement_and_interest = 'Other'; 
                $invovlement_interest_volunteering = $member['involvement_and_interests'];
                 
            }else{
                $invovlement_interest_volunteering = $member['involvement_and_interests'];
            }



            
        if($church_info && $member['full_name'] && $member['gender'] && $member['phone_number'] && $member['email'])
        {
            // return $member['date_of_birth'];
            // return true;
        $count= $count+1;
    
            $data = array(
                'user_name' => $member['full_name'],
                'email' => $member['email'],
                'gender' => $member['gender'],
                'dob' => $member['date_of_birth'],
                'location' =>  $member['city'],
                'mobile_no' => $member['phone_number'],
                'user_type' => 3,
                'is_active' =>1,
                'state' =>  $member['state'],
                'membership_status' => $membership_status,
                'membership_status_other' => $membership_status_other,
                'hear_about_church' => $hear_about,
                'hear_about_church_other' => $hear_about_other,
                'invovlement_interest' => $involvement_and_interest,
                'invovlement_interest_volunteering' => $invovlement_interest_volunteering,
                'church_id' => $church_info->id,
                'comments' => $member['additional_comments_or_questions'],
                );
            
                $aid= DB::table('users')->insertGetId($data);}
                else{
                    continue;
                }
            }
        return json_encode(array('status' => true, 'msg' => 'Members data uploaded successfully','count'=>$count));
                
    }
}