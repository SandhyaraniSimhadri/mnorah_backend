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
use App\Exports\MemberReportsExport;
use Maatwebsite\Excel\Facades\Excel;


use App\Services\UserDataService;


class DashboardController extends Controller{
    public function __construct()
    {
    }

   
    public function get_dashboard_visitors(Request $request){
    
       

        $weeklyFeeds = DB::table('feeds as f')
        ->select(DB::raw('YEARWEEK(f.created_at) as week'), DB::raw('COUNT(*) as feeds'))
        ->where('f.deleted', '=', 0)
        ->whereBetween('f.created_at', [now()->subWeeks(7), now()])
        ->groupBy(DB::raw('YEARWEEK(f.created_at)'))
        ->get();


        $last7WeeksFeeds = DB::table('feeds as f')
        ->select(DB::raw('COUNT(*) as feeds'))
        ->where('f.deleted', '=', 0)
        ->whereBetween('f.created_at', [now()->subWeeks(7), now()])
        ->first();

    // Get feeds for the before last 7 weeks
    $beforeLast7WeeksFeeds = DB::table('feeds as f')
        ->select(DB::raw('COUNT(*) as feeds'))
        ->where('f.deleted', '=', 0)
        ->whereBetween('f.created_at', [now()->subWeeks(14), now()->subWeeks(7)])
        ->first();

    // Calculate the total counts
    $totalLast7Weeks = $last7WeeksFeeds ? $last7WeeksFeeds->feeds : 0;
    $totalBeforeLast7Weeks = $beforeLast7WeeksFeeds ? $beforeLast7WeeksFeeds->feeds : 0;

    // Calculate the total percentage difference with explicit symbols
    $totalPercentageDifference = round(($totalLast7Weeks - $totalBeforeLast7Weeks) / ($totalBeforeLast7Weeks ?: 1) * 100, 1);

    // Format the result with explicit symbols
    $formattedResult = ($totalPercentageDifference >= 0 ? '+' : '') . number_format($totalPercentageDifference, 1) . '%';




        $feedsArray = $weeklyFeeds->pluck('feeds')->toArray();
        $totalFeeds = array_sum($feedsArray);

        $feeds=array(
            'weekly_feeds' => $feedsArray,
            'total_feeds' => $totalFeeds,
            'total_percentage_difference' => $formattedResult
        );
        $data = array(
            'status' => true,
            'feed_data'=>$feeds,
            'visitor_data'=>$visitors,

        );

    
        return response()->json($data);
    }
    public function get_dashboard_feed(Request $request){
    
       
        return response()->json($data);
    }

    public function get_dashboard_members(Request $request){
    
        $weekly_visitors = DB::table('visitors as v')
        ->select(DB::raw('YEARWEEK(v.created_at) as week'), DB::raw('COUNT(*) as visitors'))
        ->where('v.deleted', '=', 0)
        ->groupBy(DB::raw('YEARWEEK(v.created_at)'))
        ->get();

        $visitors_array = $weekly_visitors->pluck('visitors')->toArray();
        $total_visitors = array_sum($visitors_array);

        $visitors=array(
            'weekly_visitors' => $visitors_array,
            'total_visitors' => $total_visitors
        );



        $weekly_active_users = DB::table('users as u')
            ->leftJoin('churches as c', 'u.church_id', '=', 'c.id')
            ->select(DB::raw('YEARWEEK(u.created_at) as week'), DB::raw('COUNT(*) as active_users'))
            ->where('u.user_type', '=', 3)
            ->where('u.is_active', '=', 1)
            ->where('u.deleted', '=', 0)
            ->groupBy(DB::raw('YEARWEEK(u.created_at)'))
            ->get();
    
        // Extract active user counts and calculate the total sum
        $active_users_array = $weekly_active_users->pluck('active_users')->toArray();
        $total_active_users = array_sum($active_users_array);
    
        $users=array(
            'weekly_active_users' => $active_users_array,
            'total_active_users' => $total_active_users
        );
        

        $weekly_prayer_requests = DB::table('prayer_requests as p')
        ->select(DB::raw('YEARWEEK(p.created_at) as week'), DB::raw('COUNT(*) as requests'))
        ->where('p.deleted', '=', 0)
        ->groupBy(DB::raw('YEARWEEK(p.created_at)'))
        ->get();

   
        $weekly_prayer_requests = $weekly_prayer_requests->pluck('requests')->toArray();
        $total_prayer_requests = array_sum($weekly_prayer_requests);

        $prayer_requests=array(
            'weekly_prayer_requests' => $weekly_prayer_requests,
            'total_prayer_requests' => $total_prayer_requests
        );



        $weekly_testimony = DB::table('testimony as t')
        ->select(DB::raw('YEARWEEK(t.created_at) as week'), DB::raw('COUNT(*) as testimony'))
        ->where('t.deleted', '=', 0)
        ->groupBy(DB::raw('YEARWEEK(t.created_at)'))
        ->get();

   
        $weekly_testimony = $weekly_testimony->pluck('testimony')->toArray();
        $total_testimony = array_sum($weekly_testimony);

        $testimony=array(
            'weekly_testimony' => $weekly_testimony,
            'total_testimony' => $total_testimony
        );


        $weekly_feeds = DB::table('feeds as f')
        ->select(DB::raw('YEARWEEK(f.created_at) as week'), DB::raw('COUNT(*) as feeds'))
        ->where('f.deleted', '=', 0)
        ->groupBy(DB::raw('YEARWEEK(f.created_at)'))
        ->get();

   
        $weekly_feeds = $weekly_feeds->pluck('feeds')->toArray();
        $total_feeds = array_sum($weekly_feeds);

        $feeds=array(
            'weekly_feeds' => $weekly_feeds,
            'total_feeds' => $total_feeds
        );



        $weekly_life_group_members = DB::table('lifegroups as l')
        ->select(DB::raw('YEARWEEK(l.created_at) as week'),   DB::raw('SUM(l.members_count) as members_sum'))
        ->where('l.deleted', '=', 0)
        ->groupBy(DB::raw('YEARWEEK(l.created_at)'))
        ->get();

   
        $weekly_life_group_members = $weekly_life_group_members->pluck('members_sum')->toArray();
        $total_life_group_members = array_sum($weekly_life_group_members);

        $life_group_members=array(
            'weekly_life_group_members' => $weekly_life_group_members,
            'total_life_group_members' => $total_life_group_members
        );

          
        $weekly_churches = DB::table('churches as c')
        ->select(DB::raw('YEARWEEK(c.created_on) as week'), DB::raw('COUNT(*) as churches'))
        ->where('c.deleted', '=', 0)
        ->groupBy(DB::raw('YEARWEEK(c.created_on)'))
        ->get();

   
        $weekly_churches = $weekly_churches->pluck('churches')->toArray();
        $total_churches = array_sum($weekly_churches);

        $churches=array(
            'weekly_churches' => $weekly_churches,
            'total_churches' => $total_churches
        );


        $weekly_admins = DB::table('users as u')
        ->select(DB::raw('YEARWEEK(u.created_at) as week'), DB::raw('COUNT(*) as admins'))
        ->where('u.deleted', '=', 0)
        ->where('u.user_type', '=', 2)
        ->groupBy(DB::raw('YEARWEEK(u.created_at)'))
        ->get();

   
        $weekly_admins = $weekly_admins->pluck('admins')->toArray();
        $total_admins = array_sum($weekly_admins);

        $admins=array(
            'weekly_admins' => $weekly_admins,
            'total_admins' => $total_admins
        );

        $data = array(
            'status' => true,
            'visitors'=>$visitors,
            'members'=>$users,
            'prayer_requests'=>$prayer_requests,
            'testimony'=>$testimony,
            'feeds' => $feeds,
            'life_group_members'=>$life_group_members,
            'churches'=>$churches,
            'admins'=>$admins
        );

    
        return response()->json($data);
    }
  
    public function get_subadmin_dashboard_data(Request $request){
    
        $weekly_visitors = DB::table('visitors as v')
        ->select(DB::raw('YEARWEEK(v.created_at) as week'), DB::raw('COUNT(*) as visitors'))
        ->where('v.deleted', '=', 0)
        ->where('v.church_id', '=', $request['logged_church_id'])
        ->groupBy(DB::raw('YEARWEEK(v.created_at)'))
        ->get();

        $visitors_array = $weekly_visitors->pluck('visitors')->toArray();
        $total_visitors = array_sum($visitors_array);

        $visitors=array(
            'weekly_visitors' => $visitors_array,
            'total_visitors' => $total_visitors
        );



        $weekly_active_users = DB::table('users as u')
            ->leftJoin('churches as c', 'u.church_id', '=', 'c.id')
            ->select(DB::raw('YEARWEEK(u.created_at) as week'), DB::raw('COUNT(*) as active_users'))
            ->where('u.user_type', '=', 3)
            ->where('u.church_id','=',$request['logged_church_id'])
            ->where('u.is_active', '=', 1)
            ->where('u.deleted', '=', 0)
            ->groupBy(DB::raw('YEARWEEK(u.created_at)'))
            ->get();
    
        // Extract active user counts and calculate the total sum
        $active_users_array = $weekly_active_users->pluck('active_users')->toArray();
        $total_active_users = array_sum($active_users_array);
    
        $users=array(
            'weekly_active_users' => $active_users_array,
            'total_active_users' => $total_active_users
        );
        

        $weekly_prayer_requests = DB::table('prayer_requests as p')
        ->select(DB::raw('YEARWEEK(p.created_at) as week'), DB::raw('COUNT(*) as requests'))
        ->where('p.church_id', '=', $request['logged_church_id'])
        ->where('p.deleted', '=', 0)
        ->groupBy(DB::raw('YEARWEEK(p.created_at)'))
        ->get();

   
        $weekly_prayer_requests = $weekly_prayer_requests->pluck('requests')->toArray();
        $total_prayer_requests = array_sum($weekly_prayer_requests);

        $prayer_requests=array(
            'weekly_prayer_requests' => $weekly_prayer_requests,
            'total_prayer_requests' => $total_prayer_requests
        );



        $weekly_testimony = DB::table('testimony as t')
        ->select(DB::raw('YEARWEEK(t.created_at) as week'), DB::raw('COUNT(*) as testimony'))
        ->where('t.deleted', '=', 0)
        ->where('t.church_id', '=', $request['logged_church_id'])
        ->groupBy(DB::raw('YEARWEEK(t.created_at)'))
        ->get();

   
        $weekly_testimony = $weekly_testimony->pluck('testimony')->toArray();
        $total_testimony = array_sum($weekly_testimony);

        $testimony=array(
            'weekly_testimony' => $weekly_testimony,
            'total_testimony' => $total_testimony
        );


        $weekly_feeds = DB::table('feeds as f')
        ->select(DB::raw('YEARWEEK(f.created_at) as week'), DB::raw('COUNT(*) as feeds'))
        ->where('f.deleted', '=', 0)
        ->where('f.church_id', '=', $request['logged_church_id'])
        ->groupBy(DB::raw('YEARWEEK(f.created_at)'))
        ->get();

   
        $weekly_feeds = $weekly_feeds->pluck('feeds')->toArray();
        $total_feeds = array_sum($weekly_feeds);

        $feeds=array(
            'weekly_feeds' => $weekly_feeds,
            'total_feeds' => $total_feeds
        );



        $weekly_life_group_members = DB::table('lifegroups as l')
        ->select(DB::raw('YEARWEEK(l.created_at) as week'),   DB::raw('SUM(l.members_count) as members_sum'))
        ->where('l.deleted', '=', 0)
        ->where('l.church_id', '=', $request['logged_church_id'])
        ->groupBy(DB::raw('YEARWEEK(l.created_at)'))
        ->get();

   
        $weekly_life_group_members = $weekly_life_group_members->pluck('members_sum')->toArray();
        $total_life_group_members = array_sum($weekly_life_group_members);

        $life_group_members=array(
            'weekly_life_group_members' => $weekly_life_group_members,
            'total_life_group_members' => $total_life_group_members
        );
    
        $data = array(
            'status' => true,
            'visitors'=>$visitors,
            'members'=>$users,
            'prayer_requests'=>$prayer_requests,
            'testimony'=>$testimony,
            'feeds' => $feeds,
            'life_group_members'=>$life_group_members,
        );

    
        return response()->json($data);
    }
    
}