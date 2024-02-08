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



use App\Services\UserDataService;


class RolesAndPermissionsController extends Controller{
    public function __construct()
    {
    }
    

  public function add_role_permissions(Request $request)
    {
    //  return $request;
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
                $data = array('status' => true, 'msg' => 'Roles added successfully');
                return response()->json($data);

        } else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }

    }  
    public function get_roles(Request $request) {
        $query = DB::table('roles as r')
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
            'p.delete'
        )
        ->leftJoin('churches as c', 'r.church_id', '=', 'c.id')
        ->crossJoin('modules as m') // Cross join with modules
        ->leftJoin('permissions as p', function ($join) {
            $join->on('p.role_id', '=', 'r.id')
                 ->on('p.module_id', '=', 'm.id');
        })
        ->where('r.deleted', '=', 0)
        ->orderBy('r.created_at', 'DESC');
    
             
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
    
            return [
                'role_id' => $firstRoleItem->role_id,
                'role_name' => $firstRoleItem->role_name,
                'church_id' => $firstRoleItem->church_id,
                'avatar' => $firstRoleItem->avatar,
                'church_name' => $firstRoleItem->church_name,
                'modules' => $modules->values(),
            ];
        });
    
        return response()->json(['status' => true, 'data' => $groupedRoles->values()]);
    }
    

    public function get_single_role(Request $request)
    {
        $query = DB::table('roles as r')
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
            'p.delete'
        )
        ->leftJoin('churches as c', 'r.church_id', '=', 'c.id')
        ->crossJoin('modules as m') // Cross join with modules
        ->leftJoin('permissions as p', function ($join) {
            $join->on('p.role_id', '=', 'r.id')
                 ->on('p.module_id', '=', 'm.id');
        })
        // ->where('p.role_id','=',$request->id)
        ->where('r.deleted', '=', 0)
        ->orderBy('r.created_at', 'DESC');
    
        if ($request->id !== null) {
            $query->where('r.id', '=', $request->id);
        }
    
        if ($request['logged_user_type'] == 1) {
            $requests_info = $query->get();
        } else if ($request['logged_user_type'] == 2) {
            $requests_info = $query->where('r.church_id', '=', $request['logged_church_id'])->get();
        }
    
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
    
            return [
                'role_id' => $firstRoleItem->role_id,
                'role_name' => $firstRoleItem->role_name,
                'church_id' => $firstRoleItem->church_id,
                'avatar' => $firstRoleItem->avatar,
                'church_name' => $firstRoleItem->church_name,
                'modules' => $modules->values(),
            ];
        });
    
        return response()->json(['status' => true, 'data' => $groupedRoles->values()]);
    }
    
    
    
    
    public function update_role(REQUEST $request){
        $image=null;
        $date = date('Y-m-d H:i:s');

        if ($request->hasFile('image')) {
            // return $request->hasFile('homeTeamLogo')
            $image = $request->file('image')->store('images', 'public');
            $image = 'storage/'.$image;
        }
       $role_permissions = json_decode($request->role_permissions, true);
        // return $role_permissions;
        $update_data=DB::table('roles')
        ->where('id','=',$request->id)
        ->update([
            'church_id' => $request->church_id,
            'role_name' => $request->role_name,
            'last_modified' => $date
           
        ]);
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
                'role_id' => $request->id,
                'read' => $new_permission['read'],
                'update' => $new_permission['update'],
                'create' => $new_permission['create'],
                'delete' => $new_permission['delete'],
                'last_modified'=>$date

                );
    
                $pid= DB::table('permissions')->insertGetId($insert_permission);
            }}
        }
        if($update_data || $pid){
            $data = array('status' => true, 'msg' => 'Role details updated successfully');
            return response()->json($data);
            } 
        else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }
    }
    public function delete_role(REQUEST $request){
        $deleted_info=DB::table('roles')
        ->where('id','=',$request->id)
        ->update([
            'deleted'=>1,
        ]);
        $deleted_permission_info=DB::table('permissions')
        ->where('role_id','=',$request->id)
        ->update([
            'deleted'=>1,
        ]);
        if($deleted_info){
            $data = array('status' => true, 'msg' => 'Role deleted successfully');
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

    
 
    public function get_modules(REQUEST $request){
        $modulesAndPermissions = DB::table('modules')
        ->leftJoin('permissions', 'modules.id', '=', 'permissions.module_id')
        ->where('modules.deleted', '=', 0)
        ->select('modules.*', 'permissions.*',DB::raw('modules.id as module_pid'))
        ->get();

        $data = array('status' => true, 'data' => $modulesAndPermissions);
        
        return response()->json($data);

    }
}