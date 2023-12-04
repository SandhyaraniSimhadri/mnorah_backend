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


class TestimonyController extends Controller{
    public function __construct()
    {
    }
    

    public function add_testimony(Request $request)
    {
        $image=null;
        if ($request->hasFile('image')) {
            // return $request->hasFile('homeTeamLogo')
            $image = $request->file('image')->store('images', 'public');
            $image = 'storage/'.$image;
        }
        $date = date('Y-m-d H:i:s');
        $data = array(
            'church_id' => $request->church_id,
            'testimony' => $request->testimony,
            'image'=>$image,
            );

            $aid= DB::table('testimony')->insertGetId($data);

        if ($aid) { 
                $data = array('status' => true, 'msg' => 'Testimony added successfully');
                return response()->json($data);

        } else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }

    }
    public function get_testimony(REQUEST $request){
        $testimony_info=DB::table('testimony as t')
        ->join('churches as c','t.church_id', '=','c.id')
        ->where('t.deleted','=',0)
        ->select('t.*','c.church_name',DB::raW('t.image as avatar'))
        ->orderBy('t.created_at','DESC')
        ->get();
        $data = array('status' => true, 'data' => $testimony_info);
        return response()->json($data);
           
    }

    public function get_single_testimony(REQUEST $request){
        $testimony_info=DB::table('testimony as t')
        ->join('churches as c','t.church_id', '=','c.id')
        ->where('t.id','=',$request->id)
        ->select('t.*','c.church_name',DB::raW('t.image as avatar'))
        ->first();

        $data = array('status' => true, 'data' => $testimony_info);
           
        return response()->json($data);

    }
    
    public function update_testimony(REQUEST $request){
        $image=null;
        if ($request->hasFile('image')) {
            // return $request->hasFile('homeTeamLogo')
            $image = $request->file('image')->store('images', 'public');
            $image = 'storage/'.$image;
        }
        $update_data=DB::table('testimony')
        ->where('id','=',$request->id)
        ->update([
            'church_id' => $request->church_id,
            'testimony' => $request->testimony,
        ]);
        if($update_data){
            $data = array('status' => true, 'msg' => 'Testimony details updated successfully');
            return response()->json($data);
            } 
        else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }
}
public function delete_testimony(REQUEST $request){
    $deleted_info=DB::table('testimony')
    ->where('id','=',$request->id)
    ->update([
        'deleted'=>1,
    ]);
 
    if($deleted_info){
        $data = array('status' => true, 'msg' => 'Testimony deleted successfully');
        return response()->json($data);
        } 
    else {
        // return true;
        $data = array('status' => false, 'msg' => 'Failed');
        return response()->json($data);
    }
}

}