<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Imports\TestimonyImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TestimoniesReportExport;

use App;
use Response;



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
            'title'=>$request->title,
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
        $query=DB::table('testimony as t')
        ->join('churches as c','t.church_id', '=','c.id')
        ->where('t.deleted','=',0)
        ->select('t.*','c.church_name',DB::raW('t.image as avatar'))
        ->orderBy('t.created_at','DESC');


        if ($request['logged_user_type'] == 1) {
            $testimony_info = $query->get();
        } else if ($request['logged_user_type'] == 2) {
            $testimony_info = $query->where('t.church_id', '=', $request['logged_church_id'])->get();
        }

        return response()->json(['status' => true, 'data' => $testimony_info]);
     
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
            'title'=>$request->title
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
    public function testimony_file_import(Request $request) 
    {
        $collection = Excel::toCollection(new TestimonyImport, $request->file('file'))->toArray();
        $data1 = $collection[0];
        // return $data1;
        $date = date('Y-m-d H:i:s');
        $count=0;

        foreach ($data1 as $testimony) {
    
            $church_info = DB::table('churches as c')
            ->where('c.is_active', '=', 1)
            ->where('c.deleted', '=', 0)
            ->where('c.church_name','=',$testimony['church_name']) 
            ->first();
   
            if($church_info && $testimony['title'] && $testimony['testimony'])
            {
        // return true;
                $count= $count+1;
        
                $data = array(
                    'church_id' => $church_info->id,
                    'title' => $testimony['title'],
                    'testimony' => $testimony['testimony'],
                    );
                
                $aid= DB::table('testimony')->insertGetId($data);}
            else{
                continue;
            }
        }
                return json_encode(array('status' => true, 'msg' => 'Testimony data uploaded successfully','count'=>$count));
            
    }
    public function download_testimony_sample()
    {
        $filepath = public_path('samples/testimony_sample.csv');
        // return $filepath;
        return Response::download($filepath);
    }
    public function get_testimonies_report(Request $request)
    {
        // Assuming 'rows' is an array in the request
        $rows = $request->input('rows');
        // return $rows;
        // Additional validation if needed
        if (!is_array($rows)) {
            return response()->json(['error' => 'Invalid data format'], 400);
        }
    
        return Excel::download(new TestimoniesReportExport($rows), 'reports' . '.csv');
    }

}