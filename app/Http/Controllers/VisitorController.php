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
        $visitor_info=DB::table('visitors as v')
        ->join('churches as c','c.id', '=','v.church_id')
        ->where('v.deleted','=',0)
        ->select('v.*','c.church_name',DB::raw('v.image as avatar'))
        ->orderBy('v.created_at','DESC')
        ->get();
        $data = array('status' => true, 'data' => $visitor_info);
        return response()->json($data);
           
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

}