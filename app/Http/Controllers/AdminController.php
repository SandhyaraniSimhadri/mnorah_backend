<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Mail\SubAdminInvitation;

use App;



use App\Services\UserDataService;


class AdminController extends Controller{
    public function __construct()
    {
    }
    

    public function add_subadmin(Request $request)
    {
        $date = date('Y-m-d H:i:s');
        $email = $request->input('email');
        $md5_password = md5('123456');

        $user_info=DB::table('users')
        ->where('email','=',$email)
        ->first();
        if($user_info){
            $data = array('status' => false, 'msg' => 'Email already existed, try with another email.');
            return response()->json($data);
        }
        else{
        $data = array(
            'user_name' => $request->full_name,
            'email' => $request->email,
            'gender' => $request->gender,
            'dob' => $request->dob,
            'mobile_no' => $request->phone_number,
            'location' => $request->city,
            'password' => $md5_password,
            'last_login'=>$date,
            'church_id' => $request->church_id,
            'user_type' =>2,
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

                $admins_count=DB::table('churches')
                ->where('id','=',$request->church_id)
                ->select('admins_count')
                ->first();
                $update_admins_count=DB::table('churches')
                ->where('id','=',$request->church_id)
                ->update([
                    'admins_count' => $admins_count->admins_count+1
                ]);
                
                $data = array('status' => true, 'msg' => 'Admin added successfully');
                return response()->json($data);

        } else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }
    }
    }
    public function get_admins(REQUEST $request){
        $admin_info=DB::table('users as u')
        ->leftJoin('churches as c','u.church_id', '=','c.id')
        ->where('u.user_type','=',2)
        ->where('u.deleted','=',0)
        ->orderBy('u.created_at','DESC')
        ->select('u.*','c.church_name')
        ->get();
        
        $active_users=DB::table('users')
        ->where('user_type','=',2)
        ->where('is_active','=',1)
        ->where('deleted','=',0)
        ->orderBy('created_at','DESC')
        ->count();

        $inactive_users=DB::table('users')
        ->where('user_type','=',2)
        ->where('is_active','=',0)
        ->where('deleted','=',0)
        ->orderBy('created_at','DESC')
        ->count();

        $data = array('status' => true, 'data' => $admin_info,'active_users'=>$active_users,'pending_users'=>$inactive_users);
           
        return response()->json($data);

    }
    public function get_single_admin(REQUEST $request){
        $admin_info=DB::table('users as u')
        ->leftJoin('churches as c','u.church_id', '=','c.id')
        ->where('u.id','=',$request->id)
        ->select('u.*','c.church_name')
        ->first();
     

        $data = array('status' => true, 'data' => $admin_info);
           
        return response()->json($data);

    }
    
    public function update_subadmin(REQUEST $request){
        $image=null;
        if ($request->hasFile('image')) {
            // return $request->hasFile('homeTeamLogo')
            $image = $request->file('image')->store('images', 'public');
            $image = 'storage/'.$image;
        }
        $admins_church_info = DB::table('users')
        ->where('id','=',$request->id)
        ->select('church_id')
        ->first();

        $admins_count=DB::table('churches')
        ->where('id','=',$admins_church_info->church_id)
        ->select('admins_count')
        ->first();

        $update_admins_count_info=DB::table('churches')
        ->where('id','=',$admins_church_info->church_id)
        ->update([
            'admins_count' => $admins_count->admins_count-1
        ]);

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
            'church_id' => $request->church_id
        ]);
        $admins_count=DB::table('churches')
        ->where('id','=',$request->church_id)
        ->select('admins_count')
        ->first();
        $update_admins_count=DB::table('churches')
        ->where('id','=',$request->church_id)
        ->update([
            'admins_count' => $admins_count->admins_count+1
        ]);
        if($update_data){
            $data = array('status' => true, 'msg' => 'Admin details updated successfully');
            return response()->json($data);
            } 
        else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }
    }
    public function delete_admin(REQUEST $request){
        $users_info=DB::table('users')
        ->where('id','=',$request->id)
        ->select('church_id')
        ->first();
        if($users_info->church_id!=0){
            $admins_count=DB::table('churches')
            ->where('id','=',$users_info->church_id)
            ->select('admins_count')
            ->first();
            $update_admins_count=DB::table('churches')
            ->where('id','=',$users_info->church_id)
            ->update([
                'admins_count' => $admins_count->admins_count-1
            ]);
        }
        $deleted_info=DB::table('users')
        ->where('id','=',$request->id)
        ->update([
            'deleted'=>1,
        ]);
     
       
        if($deleted_info){
            $data = array('status' => true, 'msg' => 'Admin deleted successfully');
            return response()->json($data);
            } 
        else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }
    }
    public function send_sub_admin_invitation(REQUEST $request){
        // return $request;
        $password=  Str::random(6);
        $md5_password = md5($password);
        $current_date = date('Y-m-d H:i:s');
        $update_data=DB::table('users')
        ->where('email','=',$request->email)
        ->update([
            'password' => $md5_password,
            'email_sent' => 1,
            'email_sent_on' => $current_date
        ]);
    
        $data = [
            'user_name' => $request->user_name,
            'email' => $request->email,
            'password'=> $password,
            'church_name'=> $request->church_name,

        ];
        // mail::to('priyankajacob85@gmail.com')->send(new SubAdminInvitation($data));
        // mail::to('jakevarkey@gmail.com')->send(new SubAdminInvitation($data));
        mail::to('sandhyasimhadri999@gmail.com')->send(new SubAdminInvitation($data));


        if($update_data){
        $data = array('status' => true, 'msg' => 'Invitation sent successfully');
        return response()->json($data);}
        else{
            $data = array('status' => false,'msg' => 'Something went wrong' );
        return response()->json($data);
        }
    }
}