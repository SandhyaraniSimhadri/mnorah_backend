<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Imports\VisitorsImport;
use App;
use Response;


use App\Services\UserDataService;


class VisitorController extends Controller{
    public function __construct()
    {
    }
    

    public function add_visitor(Request $request)
    {

        $date = date('Y-m-d H:i:s');
  
        $data = array(
            'church_id' => $request->church_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'spouse_name' => $request->spouse_name,
            'child1_name' => $request->child1_name,
            'child2_name' => $request->child2_name,
            'child3_name' => $request->child3_name,
            'child4_name' => $request->child4_name,
            'email'=>$request->email,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'city' => $request->city,
            'hear_about' => $request->hear_about,
            'hear_about_other' => $request->hear_about_other,
            'visit_date' => $request->visit_date,
            'experience' => $request->experience,
            'about_visit' => $request->about_visit,
            'suggestions' => $request->suggestions,
            'prayer_request' => $request->prayer_request,
            'comments' => $request->comments,
            'connection' => $request->connection
            );

            $aid= DB::table('visitors')->insertGetId($data);

        if ($aid) { 
                $data = array('status' => true, 'msg' => 'Visitor added successfully');
                return response()->json($data);

        } else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }

    }
    public function get_visitors(REQUEST $request){
        $query=DB::table('visitors as v')
        ->join('churches as c','c.id', '=','v.church_id')
        ->where('v.deleted','=',0)
        ->select('v.*','c.church_name',DB::raw('v.image as avatar'))
        ->orderBy('v.created_at','DESC');

        if ($request['logged_user_type'] == 1) {
            $visitor_info = $query->get();
        } else if ($request['logged_user_type'] == 2) {
            $visitor_info = $query->where('v.church_id', '=', $request['logged_church_id'])->get();
        }
        return response()->json(['status' => true, 'data' => $visitor_info]);
    }

    public function get_single_visitor(REQUEST $request){
        $visitor_info=DB::table('visitors as v')
        ->join('churches as c','c.id', '=','v.church_id')
        ->where('v.id','=',$request->id)
        ->select('v.*','c.church_name',DB::raW('v.image as avatar'))
        ->first();
        $data = array('status' => true, 'data' => $visitor_info);     
        return response()->json($data);

    }
    
    public function update_visitor(REQUEST $request){
        $image=null;
        if ($request->hasFile('image')) {
            // return $request->hasFile('homeTeamLogo')
            $image = $request->file('image')->store('images', 'public');
            $image = 'storage/'.$image;
        }
        $update_data=DB::table('visitors')
        ->where('id','=',$request->id)
        ->update([
            'image' => $image,
            'church_id' => $request->church_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'spouse_name' => $request->spouse_name,
            'child1_name' => $request->child1_name,
            'child2_name' => $request->child2_name,
            'child3_name' => $request->child3_name,
            'child4_name' => $request->child4_name,
            'email'=>$request->email,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'city' => $request->city,
            'hear_about' => $request->hear_about,
            'hear_about_other' => $request->hear_about_other,
            'visit_date' => $request->visit_date,
            'experience' => $request->experience,
            'about_visit' => $request->about_visit,
            'suggestions' => $request->suggestions,
            'prayer_request' => $request->prayer_request,
            'comments' => $request->comments,
            'connection' => $request->connection
        ]);
        if($update_data){
            $data = array('status' => true, 'msg' => 'Visitor details updated successfully');
            return response()->json($data);
            } 
        else {
            // return true;
            $data = array('status' => false, 'msg' => 'Failed');
            return response()->json($data);
        }
}
public function delete_visitor(REQUEST $request){
    $deleted_info=DB::table('visitors')
    ->where('id','=',$request->id)
    ->update([
        'deleted'=>1,
    ]);
 
    if($deleted_info){
        $data = array('status' => true, 'msg' => 'Visitor deleted successfully');
        return response()->json($data);
        } 
    else {
        // return true;
        $data = array('status' => false, 'msg' => 'Failed');
        return response()->json($data);
    }
}
public function file_import(Request $request) 
{
    $collection = Excel::toCollection(new VisitorsImport, $request->file('file'))->toArray();
    $data1 = $collection[0];
    // return $data1;
    $date = date('Y-m-d H:i:s');
    $count=0;

    foreach ($data1 as $visitor) {
        $visitor_about=null;
        $visitor_about_other=null;
        $church_info = DB::table('churches as c')
        ->where('c.is_active', '=', 1)
        ->where('c.deleted', '=', 0)
        ->where('c.church_name','=',$visitor['church_name']) 
        ->first();
        if($visitor['how_did_you_hear_about_us']!= 'Invited by a friend or family member' &&  
        $visitor['how_did_you_hear_about_us']!= 'Online search' && 
        $visitor['how_did_you_hear_about_us']!= 'Social media' &&
        $visitor['how_did_you_hear_about_us']!= 'Advertisement' ){
            $visitor_about = 'Other'; 
            $visitor_about_other = $visitor['how_did_you_hear_about_us'];
             
        }else{
            $visitor_about = $visitor['how_did_you_hear_about_us'];
        }
    if($church_info && $visitor['first_name'] && $visitor['last_name'] && $visitor['email'] && $visitor['phone_number'] && $visitor['city'])
    {
        // return true;
       $count= $count+1;
  
        $data = array(
            'church_id' => $church_info->id,
            'first_name' => $visitor['first_name'],
            'last_name' => $visitor['last_name'],
            'spouse_name' => $visitor['spouse_name'],
            'child1_name' => $visitor['child_name_1'],
            'child2_name' => $visitor['child_name_2'],
            'child3_name' => $visitor['child_name_3'],
            'child4_name' => $visitor['child_name_4'],
            'email'=>$visitor['email'],
            'phone_number' => $visitor['phone_number'],
            'address' => $visitor['address'],
            'city' => $visitor['city'],
            'hear_about' => $visitor_about,
            'hear_about_other' => $visitor_about_other,
            // 'hear_about_other' => $visitor->hear_about_other,
            'visit_date' => $visitor['date_of_visit'],
            'experience' => $visitor['how_was_your_experience_today'],
            'about_visit' => $visitor['what_did_you_enjoy_most_about_your_visit'],
            'suggestions' => $visitor['suggestions_or_improvement'],
            'prayer_request' => $visitor['prayer_requests'],
            'comments' => $visitor['additional_comments'],
            'connection' => $visitor['connection_card']
            );
          
            $aid= DB::table('visitors')->insertGetId($data);}
            else{
// return false;
                continue;
            }
        }
                return json_encode(array('status' => true, 'msg' => 'Visitors data uploaded successfully','count'=>$count));
            
    }

    public function download_visitor_sample()
    {
        $filepath = public_path('samples/visitor_sample.csv');
        // return $filepath;
        return Response::download($filepath);
    }
}

