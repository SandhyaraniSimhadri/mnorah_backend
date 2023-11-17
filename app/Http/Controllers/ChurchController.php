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


class ChurchController extends Controller{
    public function __construct()
    {
    }
    

    public function add_church(Request $request)
    {

        $date = date('Y-m-d H:i:s');
  
        $data = array(
            'admin_id' => $request->admin_id,
            'church_name' => $request->church_name,
            'pastor_name' => $request->pastor_name,
            'location' => $request->location,
            'users' => $request->users,
            'denomination' => $request->denomination,
            'language'=>$request->language,
            'city' => $request->city,
            'country' => $request->country,
            'church_address' => $request->church_address,
            'mobile_no' => $request->contact_number,
            'website' => $request->website,
            'email' => "",
            'is_active'=>1,
            );

            $aid= DB::table('churches')->insertGetId($data);

        if ($aid) { 
                $data = array('status' => true, 'msg' => 'Church added successfully successfull!');
                return response()->json($data);

        } else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }

    }
    public function get_churches(REQUEST $request){
        $church_info=DB::table('churches as c')
        ->join('users as u','u.id', '=','c.admin_id')
        ->where('c.is_active','=',1)
        ->where('c.deleted','=',0)
        ->select('c.*','u.user_name',DB::raW('c.image as avatar'))
        ->orderBy('c.created_on','DESC')
        ->get();
        $data = array('status' => true, 'data' => $church_info);
        return response()->json($data);
           
    }

    public function get_single_church(REQUEST $request){
        $church_info=DB::table('churches as c')
        ->join('users as u','u.id', '=','c.admin_id')
        ->where('c.id','=',$request->id)
        ->select('c.*','u.user_name',DB::raW('c.image as avatar'))
        ->first();
        // reutrn $church_info;

        $data = array('status' => true, 'data' => $church_info);
           
        return response()->json($data);

    }
    
    public function update_church(REQUEST $request){
        $image=null;
        if ($request->hasFile('image')) {
            // return $request->hasFile('homeTeamLogo')
            $image = $request->file('image')->store('images', 'public');
            $image = 'storage/'.$image;
        }
        $update_data=DB::table('churches')
        ->where('id','=',$request->id)
        ->update([
            'admin_id' => $request->admin_id,
            'church_name' => $request->church_name,
            'pastor_name' => $request->pastor_name,
            'location' => $request->location,
            'users' => $request->users,
            'denomination' => $request->denomination,
            'language'=>$request->language,
            'city' => $request->city,
            'country' => $request->country,
            'church_address' => $request->church_address,
            'mobile_no' => $request->mobile_no,
            'website' => $request->website,
            'image'=>$image,
            'email' => "",
            'is_active'=>1,
        ]);
        if($update_data){
            $data = array('status' => true, 'msg' => 'Church details updated successfully');
            return response()->json($data);
            } 
        else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }
}
public function delete_church(REQUEST $request){
    $deleted_info=DB::table('churches')
    ->where('id','=',$request->id)
    ->update([
        'deleted'=>1,
    ]);
 
    if($deleted_info){
        $data = array('status' => true, 'msg' => 'Church deleted successfully');
        return response()->json($data);
        } 
    else {
        // return true;
        $data = array('status' => false, 'msg' => 'Failed');
        return response()->json($data);
    }
}

}