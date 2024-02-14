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
            'admins_count' => $request->admins_count,
            'church_name' => $request->church_name,
            'pastor_name' => $request->pastor_name,
            'location' => $request->location,
            'users' => $request->users,
            'denomination' => $request->denomination,
            'language'=>$request->language,
            'city' => $request->city,
            'country' => $request->country,
            'address' => $request->address,
            'mobile_no' => $request->contact_number,
            'website' => $request->website,
            'email' => "",
            'is_active'=>1,
            );
            $aid= DB::table('churches')->insertGetId($data);
            if($request->admins_list){
            $request->admins_list=explode(',', $request->admins_list);
            $updated_info=DB::table('users')
            ->whereIn('id',$request->admins_list)
            ->update([
                'church_id'=>$aid,
            ]);}

        if ($aid) { 
                $data = array('status' => true, 'msg' => 'Church added successfully');
                return response()->json($data);

        } else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }

    }
    public function get_churches(REQUEST $request){
        $query = DB::table('churches as c')
        ->leftJoin('users as u', 'c.id', '=', 'u.church_id')
        ->where('c.is_active', '=', 1)
        ->where('c.deleted', '=', 0)
        ->where(function ($query) {
            $query->where('u.deleted', '=', 0)
                  ->orWhereNull('u.deleted'); 
        })
        ->where(function ($query) {
            $query->where('u.user_type', '=', 2)
                  ->orWhereNull('u.user_type'); 
        })
        ->select(
            'c.*', DB::raw('c.image as avatar'),
            DB::raw('GROUP_CONCAT(u.user_name) as admins'),
            DB::raw('GROUP_CONCAT(u.id) as admin_ids')
        )
        ->orderBy('c.created_at', 'DESC')
        ->groupBy('c.id', 'c.admins_count', 'c.email', 'c.image', 'c.location', 'c.mobile_no', 'c.church_name', 'c.users', 'c.pastor_name', 'c.denomination', 'c.language', 'c.city', 'c.country', 'c.address', 'c.website', 'c.is_active', 'c.created_at', 'c.deleted');
        
        if ($request['logged_user_type'] == 1) {
            $church_info = $query->get();
        } else if ($request['logged_user_type'] == 2) {
            $church_info = $query->where('c.id', '=', $request['logged_church_id'])->get();
        }

    
    $data = array('status' => true, 'data' => $church_info);
    return response()->json($data);
        }    
    public function get_single_church(REQUEST $request){
        $church_info = DB::table('churches as c')
        ->leftJoin('users as u', 'c.id', '=', 'u.church_id')
        ->leftJoin('countries as co','co.id','=','c.country')
        ->where('c.is_active', '=', 1)
        ->where('c.deleted', '=', 0)
        ->where('c.id','=',$request->id)
        ->where(function ($query) {
            $query->where('u.is_active', '=', 1)
                  ->orWhereNull('u.is_active'); 
        })
        ->where(function ($query) {
            $query->where('u.user_type', '=', 2)
                  ->orWhereNull('u.user_type'); 
        })
        ->where(function ($query) {
            $query->where('u.deleted', '=', 0)
                  ->orWhereNull('u.deleted');
        })
        ->select(
            'c.*', DB::raw('co.country as country_name'),DB::raw('c.image as avatar'),
            DB::raw('GROUP_CONCAT(u.user_name) as admins'),
            DB::raw('GROUP_CONCAT(u.id) as admin_ids')
        )
        ->orderBy('c.created_at', 'DESC')
        ->groupBy('c.id', 'c.admins_count', 'c.email', 'c.image', 'co.country','c.location', 'c.mobile_no', 'c.church_name', 'c.users', 'c.pastor_name', 'c.denomination', 'c.language', 'c.city', 'c.country', 'c.address', 'c.website', 'c.is_active', 'c.created_at', 'c.deleted')
        ->first();
    
    $data = array('status' => true, 'data' => $church_info);
    return response()->json($data);

    }
    
    public function update_church(REQUEST $request){
        $image=null;
        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('images', 'public');
            $image = 'storage/'.$image;
        }
      
        $update_data=DB::table('churches')
        ->where('id','=',$request->id)
        ->update([
            'admins_count' => $request->admins_count,
            'church_name' => $request->church_name,
            'pastor_name' => $request->pastor_name,
            'location' => $request->location,
            'users' => $request->users,
            'denomination' => $request->denomination,
            'language'=>$request->language,
            'city' => $request->city,
            'country' => $request->country,
            'address' => $request->address,
            'mobile_no' => $request->mobile_no,
            'website' => $request->website,
            'image'=>$image,
            'email' => "",
            'is_active'=>1,
        ]);
        $request->admins_list=explode(',', $request->admins_list);
        $updated_info=DB::table('users')
        ->where('church_id',$request->id)
        ->update([
            'church_id'=>0,
        ]);

        $new_updated_info=DB::table('users')
        ->whereIn('id',$request->admins_list)
        ->update([
            'church_id'=>$request->id,
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
        $updated_info=DB::table('users')
        ->where('church_id',$request->id)
        ->update([
            'deleted'=>1,
            'is_active'=>0
        ]);
        $updated_info=DB::table('events')
        ->where('church_id',$request->id)
        ->update([
            'deleted'=>1,
        ]);
        
        $updated_info=DB::table('feeds')
        ->where('church_id',$request->id)
        ->update([
            'deleted'=>1,
        ]);
        
        $updated_info=DB::table('lifegroups')
        ->where('church_id',$request->id)
        ->update([
            'deleted'=>1,
        ]);
        
        $updated_info=DB::table('prayer_requests')
        ->where('church_id',$request->id)
        ->update([
            'deleted'=>1,
        ]);
        
        $updated_info=DB::table('testimony')
        ->where('church_id',$request->id)
        ->update([
            'deleted'=>1,
        ]);

        $updated_info=DB::table('visitors')
        ->where('church_id',$request->id)
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
    public function get_admins_for_new_church(REQUEST $request){
        $admin_info=DB::table('users')
        ->where('user_type','=',2)
        ->where('deleted','=',0)
        ->where('church_id','=',0)
        ->orderBy('created_at','DESC')
        ->get();
        $data = array('status' => true, 'data' => $admin_info);
        return response()->json($data);
    }

}