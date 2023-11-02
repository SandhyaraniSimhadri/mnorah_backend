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


class UsersController extends Controller{
    public function __construct()
    {
    }
    
    public function verify_user(Request $request)
    {
    //    dd($request);
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);
        $date = date('Y-m-d H:i:s');
        $email = $request->input('email');

        
        $md5_password = md5($request->input('password'));
        // return $md5_password;
        $user_data = DB::table('users')
           ->where('email','=',$email)
           ->where('password','=',$md5_password)
           ->select('is_active')
           ->first();

        if ($user_data) { 
            $token = Str::random(60);
            $api_token = hash('sha256', $token);
            if($user_data->is_active){
                $update_data=DB::table('users')
                ->where('email','=',$email)
                ->where('password','=',$md5_password)
                ->update([
                    'last_login' => $date,
                    'token' => $api_token,
                ]);
                $user_data = DB::table('users')
                ->where('email','=',$email)
                ->where('password','=',$md5_password)
                ->select('id','user_name','email','avatar','mobile_no','token','is_active','user_type')
                ->first();
                $data = array('status' => true, 'msg' => 'Login successfull!','data'=>$user_data);
                return response()->json($data);
            }else{
                $data = array('status' => false, 'msg' => 'Account is inactive. Please contact customer care!');
                return response()->json($data); 
            }

        } else {
            // return true;
            $data = array('status' => false, 'msg' => 'Login Failed. Please enter correct credentials');
            return response()->json($data);
        }

    }

    public function register(Request $request)
    {
    //    dd($request);
    // return $request;
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required',
            'username'=>'required',
            'phone'=>'required',
            'location' => 'required',
        ]);
        $date = date('Y-m-d H:i:s');
        $email = $request->input('email');
        $md5_password = md5($request->input('password'));

        $user_info=DB::table('super_admins')
        ->where('email','=',$email)
        ->first();
        if($user_info){
            $data = array('status' => false, 'msg' => 'Email already existed, try with another email.');

        }
        $data = array(
            'user_name' => $request->username,
            'email' => $request->email,
            'password' => $md5_password,
            'location' => $request->location,
            'mobile_no' => $request->phone,
            'church_name' => $request->churchname,
            'users' => $request->users,
            'pastor_name' => $request->pastorname,
            'denomination' => $request->denomination,
            'city' => $request->city,
            'country' => $request->country,
            'church_address' => $request->church_address,
            'website' => $request->website,
            'user_type' => 1,
            'is_active'=>1,
            'last_login'=>$date
            );

            $aid= DB::table('super_admins')->insertGetId($data);

        if ($aid) { 
            $token = Str::random(60);
            $api_token = hash('sha256', $token);
            // return $api_token;
                $update_data=DB::table('super_admins')
                ->where('email','=',$email)
                ->where('password','=',$md5_password)
                ->update([
                    'last_login' => $date,
                    'token' => $api_token,
                ]);
                $data = array('status' => true, 'msg' => 'Registration successfull!');
                return response()->json($data);

        } else {
            // return true;
            $data = array('status' => false, 'msg' => 'Registration Failed. Please enter correct details');
            return response()->json($data);
        }

    }

}