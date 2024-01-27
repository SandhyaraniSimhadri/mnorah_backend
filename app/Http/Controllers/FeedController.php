<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Imports\FeedsImport;
use App\Exports\FeedsReportExport;

use Maatwebsite\Excel\Facades\Excel;

use App;
use Response;


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
        $query=DB::table('feeds as f')
        ->join('churches as c','f.church_id', '=','c.id')
        ->where('f.deleted','=',0)
        ->select('f.*','c.church_name',DB::raW('f.image as avatar'))
        ->orderBy('f.created_at','DESC');
       
        if ($request['logged_user_type'] == 1) {
            $feeds_info = $query->get();
        } else if ($request['logged_user_type'] == 2) {
            $feeds_info = $query->where('f.church_id', '=', $request['logged_church_id'])->get();
        }
        return response()->json(['status' => true, 'data' => $feeds_info]);
           
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
public function feed_file_import(Request $request) 
{
    $collection = Excel::toCollection(new FeedsImport, $request->file('file'))->toArray();
    $data1 = $collection[0];
    // return $data1;
    $date = date('Y-m-d H:i:s');
    $count=0;

    foreach ($data1 as $feed) {
    
        $church_info = DB::table('churches as c')
        ->where('c.is_active', '=', 1)
        ->where('c.deleted', '=', 0)
        ->where('c.church_name','=',$feed['church_name']) 
        ->first();
   
    if($church_info && $feed['title'] && $feed['description'])
    {
        // return true;
       $count= $count+1;
  
        $data = array(
            'church_id' => $church_info->id,
            'title' => $feed['title'],
            'author' => $feed['author'],
            'description' => $feed['description'],
            );
          
            $aid= DB::table('feeds')->insertGetId($data);}
            else{
                continue;
            }
        }
                return json_encode(array('status' => true, 'msg' => 'Feeds data uploaded successfully','count'=>$count));
            
    }
    public function download_feed_sample()
    {
        $filepath = public_path('samples/feed_sample.csv');
        // return $filepath;
        return Response::download($filepath);
    }
    public function get_feeds_report(Request $request)
    {
        // Assuming 'rows' is an array in the request
        $rows = $request->input('rows');
        // return $rows;
        // Additional validation if needed
        if (!is_array($rows)) {
            return response()->json(['error' => 'Invalid data format'], 400);
        }
    
        return Excel::download(new FeedsReportExport($rows), 'reports' . '.csv');
    }
}