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


class LifeGroupController extends Controller{
    public function __construct()
    {
    }
    

    public function add_life_group(Request $request)
    {
     
        $date = date('Y-m-d H:i:s');
        // $request->members=$request->members->implode(',');
        $data = array(
            'church_id' => $request->church_id,
            'country' => $request->country,
            'city' => $request->city,
            'area' => $request->area,
            'members_count' => $request->members_count,
            'members' => $request->members,
            );

            $aid= DB::table('lifegroups')->insertGetId($data);

        if ($aid) { 
                $data = array('status' => true, 'msg' => 'Life group added successfully');
                return response()->json($data);

        } else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }

    }
    public function get_life_groups(REQUEST $request){
        $life_groups_info=DB::table('lifegroups as l')
        ->join('churches as c','l.church_id', '=','c.id')
        ->where('l.deleted','=',0)
        ->select('l.*','c.church_name',DB::raW('l.image as avatar'))
        ->orderBy('l.created_at','DESC')
        ->get();
        $data = array('status' => true, 'data' => $life_groups_info);
        return response()->json($data);
           
    }

    public function get_single_life_group(REQUEST $request){
        $life_groups_info=DB::table('lifegroups as l')
        ->join('churches as c','l.church_id', '=','c.id')
        ->where('l.id','=',$request->id)
        ->select('l.*','c.church_name',DB::raW('l.image as avatar'))
        ->first();

        if ($life_groups_info) {
            // Get user IDs as an array
            $userIds = explode(',', $life_groups_info->members);
        
            // Retrieve user names from the users table
            $userNames = DB::table('users')
                ->whereIn('id', $userIds)
                ->pluck('user_name')
                ->toArray();
        
            // Add user names to the $lifeGroupsInfo object
            $life_groups_info->userNames = $userNames;
            // $life_groups_info->userNames =$life_groups_info->userNames->implode(',');

   
        $data = array('status' => true, 'data' => $life_groups_info);
           
        return response()->json($data);

        }}
    
    public function update_life_group(REQUEST $request){
        $image=null;
        if ($request->hasFile('image')) {
            // return $request->hasFile('homeTeamLogo')
            $image = $request->file('image')->store('images', 'public');
            $image = 'storage/'.$image;
        }
        $update_data=DB::table('lifegroups')
        ->where('id','=',$request->id)
        ->update([
            'church_id' => $request->church_id,
            'country' => $request->country,
            'city' => $request->city,
            'area' => $request->area,
            'members_count' => $request->members_count,
            'members' => $request->members,
            'image'=>$image
        
        ]);
        if($update_data){
            $data = array('status' => true, 'msg' => 'Lifegroup details updated successfully');
            return response()->json($data);
            } 
        else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }
}
public function delete_life_group(REQUEST $request){
    $deleted_info=DB::table('lifegroups')
    ->where('id','=',$request->id)
    ->update([
        'deleted'=>1,
    ]);
 
    if($deleted_info){
        $data = array('status' => true, 'msg' => 'Lifegroup deleted successfully');
        return response()->json($data);
        } 
    else {
        // return true;
        $data = array('status' => false, 'msg' => 'Failed');
        return response()->json($data);
    }
}
public function get_members_ids(REQUEST $request){
    $member_info=DB::table('users as u')
    ->join('churches as c','u.church_id','=','c.id')
    ->where('u.user_type','=',3)
    ->where('u.deleted','=',0)
    ->where('u.is_active','=',1)
    ->select('u.id','u.user_name','c.church_name')
    ->orderBy('created_at','DESC')
    ->get();

    $data = array('status' => true, 'data' => $member_info);
       
    return response()->json($data);

}

}