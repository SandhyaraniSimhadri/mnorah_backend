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
use App\Imports\LifeGroupImport;
use App\Services\UserDataService;
use App\Exports\LifegroupsReportExport;


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
            'leader' => $request->leader,
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
        // $life_groups_info=DB::table('lifegroups as l')
        // ->join('churches as c','l.church_id', '=','c.id')
        // ->where('l.deleted','=',0)
        // ->select('l.*','c.church_name',DB::raW('l.image as avatar'))
        // ->orderBy('l.created_at','DESC')
        // ->get();
        // $data = array('status' => true, 'data' => $life_groups_info);
        // return response()->json($data);

        $query=DB::table('lifegroups as l')
        ->leftJoin('churches as c','l.church_id', '=','c.id')
        ->leftJoin('users as u','l.leader', '=','u.id')
        ->leftJoin('countries as co','co.id','=','l.country')
        ->where('l.deleted','=',0)
        ->where('c.deleted','=',0)
        ->where(function ($query) {
            $query->where('u.deleted', '=', 0)
                  ->orWhereNull('u.deleted'); 
        })
        ->select('l.*',DB::raw('co.country as country_name'),'u.user_name','c.church_name',DB::raW('l.image as avatar'))
        ->orderBy('l.created_at','DESC');

        if ($request['logged_user_type'] == 1) {
            $life_group_info = $query->get();
        } else if ($request['logged_user_type'] == 2) {
            $life_group_info = $query->where('l.church_id', '=', $request['logged_church_id'])->get();
        }

        return response()->json(['status' => true, 'data' => $life_group_info]);
           
    }

    public function get_single_life_group(REQUEST $request){
        $life_groups_info=DB::table('lifegroups as l')
        ->leftJoin('churches as c','l.church_id', '=','c.id')
        ->leftJoin('countries as co','co.id','=','l.country')
        ->leftJoin('users as u','l.leader', '=','u.id')
        ->where('l.id','=',$request->id)
        ->where('c.deleted','=',0)
        ->where(function ($query) {
            $query->where('u.deleted', '=', 0)
                  ->orWhereNull('u.deleted'); 
        })
        ->where('l.deleted','=',0)
        ->select('l.*',DB::raw('co.country as country_name'),'c.church_name','u.user_name',DB::raW('l.image as avatar'))
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
            'leader' => $request->leader,
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

        $query=DB::table('users as u')
        ->join('churches as c','u.church_id','=','c.id')
        ->where('u.user_type','=',3)
        ->where('u.deleted','=',0)
        ->select('u.id','u.user_name','c.church_name')
        ->orderBy('u.created_at','DESC');

        if ($request['logged_user_type'] == 1) {
            $member_info = $query->get();
        } else if ($request['logged_user_type'] == 2) {
            $member_info = $query->where('u.church_id', '=', $request['logged_church_id'])->get();
        }

        $data = array('status' => true, 'data' => $member_info);
        
        return response()->json($data);
    }

    public function lifegroup_file_import(Request $request) 
    {
        $collection = Excel::toCollection(new LifegroupImport, $request->file('file'))->toArray();
        $data1 = $collection[0];
        // return $data1;
        $date = date('Y-m-d H:i:s');
        $count=0;

        foreach ($data1 as $lifegroup) {
    
            $church_info = DB::table('churches as c')
            ->where('c.is_active', '=', 1)
            ->where('c.deleted', '=', 0)
            ->where('c.church_name','=',$lifegroup['church_name']) 
            ->first();
            $array = explode(',', $lifegroup['members']);
            

            if($church_info && $lifegroup['country'] && $lifegroup['city'] && $lifegroup['area'] && $lifegroup['leader'])
            {
                $count= $count+1;
                $data = array(
                    'church_id' => $church_info->id,
                    'country' => $lifegroup['country'],
                    'leader' => $lifegroup['leader'],
                    'city' => $lifegroup['city'],
                    'area' => $lifegroup['area'],
                    'members' => $lifegroup['members'],
                    'members_count' => count($array)
                    );
                
                $aid= DB::table('lifegroups')->insertGetId($data);}
            else{
                continue;
            }
        }
        return json_encode(array('status' => true, 'msg' => 'Lifegroups data uploaded successfully','count'=>$count));
            
    }
    public function download_lifegroup_sample()
    {
        $filepath = public_path('samples/lifegroup_sample.csv');
        // return $filepath;
        return Response::download($filepath);
    }
    public function get_life_groups_report(Request $request)
    {
        // Assuming 'rows' is an array in the request
        $rows = $request->input('rows');
        // return $rows;
        // Additional validation if needed
        if (!is_array($rows)) {
            return response()->json(['error' => 'Invalid data format'], 400);
        }
    
        return Excel::download(new LifegroupsReportExport($rows), 'reports' . '.csv');
    }
    
}