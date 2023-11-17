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


class FeedController extends Controller{
    public function __construct()
    {
    }
    

    public function add_feed(Request $request)
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
            'author' => $request->author,
            'title' => $request->title,
            'description' => $request->description,
            'image'=>$image,
            );

            $aid= DB::table('feeds')->insertGetId($data);

        if ($aid) { 
                $data = array('status' => true, 'msg' => 'Feed added successfully');
                return response()->json($data);

        } else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }

    }
    public function get_feeds(REQUEST $request){
        $feeds_info=DB::table('feeds as f')
        ->join('churches as c','f.church_id', '=','c.id')
        ->where('f.deleted','=',0)
        ->select('f.*','c.church_name',DB::raW('f.image as avatar'))
        ->orderBy('f.created_at','DESC')
        ->get();
        $data = array('status' => true, 'data' => $feeds_info);
        return response()->json($data);
           
    }

    public function get_single_feed(REQUEST $request){
        $feeds_info=DB::table('feeds as f')
        ->join('churches as c','f.church_id', '=','c.id')
        ->where('f.id','=',$request->id)
        ->select('f.*','c.church_name',DB::raW('f.image as avatar'))
        ->first();
        // reutrn $feeds_info;

        $data = array('status' => true, 'data' => $feeds_info);
           
        return response()->json($data);

    }
    
    public function update_feed(REQUEST $request){
        $image=null;
        if ($request->hasFile('image')) {
            // return $request->hasFile('homeTeamLogo')
            $image = $request->file('image')->store('images', 'public');
            $image = 'storage/'.$image;
        }
        $update_data=DB::table('feeds')
        ->where('id','=',$request->id)
        ->update([
            'church_id' => $request->church_id,
            'title' => $request->title,
            'author' => $request->author,
            'description' => $request->description,
        
        ]);
        if($update_data){
            $data = array('status' => true, 'msg' => 'Feed details updated successfully');
            return response()->json($data);
            } 
        else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }
}
public function delete_feed(REQUEST $request){
    $deleted_info=DB::table('feeds')
    ->where('id','=',$request->id)
    ->update([
        'deleted'=>1,
    ]);
 
    if($deleted_info){
        $data = array('status' => true, 'msg' => 'Feed deleted successfully');
        return response()->json($data);
        } 
    else {
        // return true;
        $data = array('status' => false, 'msg' => 'Failed');
        return response()->json($data);
    }
}

}