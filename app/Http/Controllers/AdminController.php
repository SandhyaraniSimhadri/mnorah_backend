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
            if($request->role_id ==0){
                $total_data = json_decode($request->role_permissions, true);
  
                $date = date('Y-m-d H:i:s');
                $data = array(
                    'church_id' => $request->church_id,
                    'role_name' => $request->role,
                    'created_by' =>1,
                    'last_modified' => $date
                    );
        
                    $aid= DB::table('roles')->insertGetId($data);
                   
                    foreach ($total_data as $permission) {
                        if($permission['read']==false){
                            $permission['read']=0;
                        }else{
                            $permission['read']=1; 
                        }
                        if($permission['update']==false){
                            $permission['update']=0;
                        }else{
                            $permission['update']=1;
                        }
                        if($permission['create']==false){
                            $permission['create']=0;
                        }
                        else{
                            $permission['create']=1;
                        }
                        if($permission['delete']==false){
                            $permission['delete']=0;
                        }else{
                            $permission['delete']=1;
                        }
                        if($permission['read'] || $permission['update'] || $permission['create'] || $permission['delete']){
                        $insert_permission = array(
                            'module_id' => $permission['module_pid'],
                            'role_id' => $aid,
                            'read' => $permission['read'],
                            'update' => $permission['update'],
                            'create' => $permission['create'],
                            'delete' => $permission['delete'],
                            'last_modified'=>$date
        
                            );
                
                            $pid= DB::table('permissions')->insertGetId($insert_permission);
                        }
                    }
        
                if ($aid) { 
                    $request->role_id  = $aid;
                }
            }
           
        if($request->role_id){
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
            'is_active'=>1,
            'role_id'=>$request->role_id
            );

            $aid= DB::table('users')->insertGetId($data);}

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
        // return $request;
        $query = 
        DB::table('users as u')
        ->select(
            'r.id as role_id',
            'r.role_name as role_name',
            'c.church_name',
            'r.image as avatar',
            'r.church_id',
            'm.id as module_id',
            'm.module_name',
            'm.created_at as module_created_at',
            'p.id as permission_id',
            'p.read',
            'p.update',
            'p.create',
            'p.delete',
            'u.*'
        )
        ->Join('roles as r', 'u.role_id', '=', 'r.id')
        ->leftJoin('churches as c', 'r.church_id', '=', 'c.id')
        ->crossJoin('modules as m') // Cross join with modules
        ->leftJoin('permissions as p', function ($join) {
            $join->on('p.role_id', '=', 'r.id')
                 ->on('p.module_id', '=', 'm.id');
        })
        // ->where('p.role_id','=',$request->id)
        ->where('r.deleted', '=', 0)
        ->where('u.id','=',$request->id)
        ->orderBy('r.created_at', 'DESC');
    
      
        // return ( $request['logged_user_type']);
    
        if ($request['logged_user_type'] == 1) {
            $requests_info = $query->get();
        } else if ($request['logged_user_type'] == 2) {
            $requests_info = $query->where('r.church_id', '=', $request['logged_church_id'])->get();
        }
        // return $requests_info;
        // Transform the result to group by role and modules
        $groupedRoles = collect($requests_info)->groupBy('role_id')->map(function ($roleGroup) {
            $firstRoleItem = $roleGroup->first();
    
            $modules = $roleGroup->groupBy('module_id')->map(function ($moduleGroup) {
                $firstModuleItem = $moduleGroup->first();
    
                $permissions = $moduleGroup->map(function ($permissionItem) {
                    return [
                        'permission_id' => $permissionItem->permission_id,
                        'read' => $permissionItem->read,
                        'update' => $permissionItem->update,
                        'create' => $permissionItem->create,
                        'delete' => $permissionItem->delete,
                    ];
                });
    
                return [
                    'module_id' => $firstModuleItem->module_id,
                    'module_name' => $firstModuleItem->module_name,
                    'module_created_at' => $firstModuleItem->module_created_at,
                    'permissions' => $permissions->values(),
                ];
            });
            // return $firstRoleItem;
            return [
                'role_id' => $firstRoleItem->role_id,
                'role_name' => $firstRoleItem->role_name,
                'church_id' => $firstRoleItem->church_id,
                'avatar' => $firstRoleItem->avatar,
                'church_name' => $firstRoleItem->church_name,
                'user'=>$firstRoleItem,
                'modules' => $modules->values(),
            ];
        });
    
        return response()->json(['status' => true, 'data' => $groupedRoles->values()]);

        // $admin_info=DB::table('users as u')
        // ->leftJoin('churches as c','u.church_id', '=','c.id')
        // ->where('u.id','=',$request->id)
        // ->select('u.*','c.church_name')
        // ->first();
     

        // $data = array('status' => true, 'data' => $admin_info);
           
        // return response()->json($data);

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
        if($request->role_id ==0){
            $role_permissions = json_decode($request->role_permissions, true);
            // return $role_permissions;
            $date = date('Y-m-d H:i:s');
            $data = array(
                'church_id' => $request->church_id,
                'role_name' => $request->role_name,
                'created_by' =>1,
                'last_modified' => $date
                );
    
                $aid= DB::table('roles')->insertGetId($data);
               
                foreach($role_permissions as $permission ){
                    $new_permission = $permission['permissions'][0];
                    if($new_permission['read']==false){
                        $new_permission['read']=0;
                    }else{
                        $new_permission['read']=1; 
                    }
                    if($new_permission['update']==false){
                        $new_permission['update']=0;
                    }else{
                        $new_permission['update']=1;
                    }
                    if($new_permission['create']==false){
                        $new_permission['create']=0;
                    }
                    else{
                        $new_permission['create']=1;
                    }
                    if($new_permission['delete']==false){
                        $new_permission['delete']=0;
                    }else{
                        $new_permission['delete']=1;
                    }
                    if($new_permission['read'] || $new_permission['update'] || $new_permission['create'] || $new_permission['delete']){
                       if($new_permission['permission_id']){
                        $update_data=DB::table('permissions')
                        ->where('id','=',$new_permission['permission_id'])
                        ->update([
                            'read' => $new_permission['read'],
                            'update' => $new_permission['update'],
                            'create' => $new_permission['create'],
                            'delete' => $new_permission['delete'],
                            'last_modified' => $date
                        ]);
            
                    }
                       else{
                        $insert_permission = array(
                            'module_id' => $permission['module_id'],
                            'role_id' => $aid,
                            'read' => $new_permission['read'],
                            'update' => $new_permission['update'],
                            'create' => $new_permission['create'],
                            'delete' => $new_permission['delete'],
                            'last_modified'=>$date
            
                            );
                
                            $pid= DB::table('permissions')->insertGetId($insert_permission);
                        }}
                    }
    
            if ($aid) { 
                $request->role_id  = $aid;
            }
        }
        if($request->role_id){
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
            'church_id' => $request->church_id,
            'role_id' => $request->role_id
        ]);}
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
            'email_sent_on' => $current_date,
            'is_active'=>1
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