<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('verify_user', 'UsersController@verify_user');
Route::post('register', 'UsersController@register');

Route::middleware('api_check')->post('add_subadmin', 'AdminController@add_subadmin');
Route::middleware('api_check')->get('get_admins', 'AdminController@get_admins');
Route::post('delete_admin', 'AdminController@delete_admin');
Route::post('get_single_admin', 'AdminController@get_single_admin');


Route::middleware('api_check')->post('add_church', 'ChurchController@add_church');
Route::get('get_churches', 'ChurchController@get_churches');
Route::post('get_single_church', 'ChurchController@get_single_church');
Route::post('delete_church', 'ChurchController@delete_church');


Route::group(['middleware' => ['cors']], function () {
Route::post('update_church', 'ChurchController@update_church');
Route::post('update_member', 'MembersController@update_member');
Route::post('update_feed', 'FeedController@update_feed');
Route::post('update_subadmin', 'AdminController@update_subadmin');
Route::post('update_event', 'EventController@update_event');

});

Route::post('get_single_member', 'MembersController@get_single_member');
Route::middleware('api_check')->get('get_members', 'MembersController@get_members');
Route::middleware('api_check')->post('add_member', 'MembersController@add_member');
Route::post('delete_member', 'MembersController@delete_member');

Route::post('get_single_feed', 'FeedController@get_single_feed');
Route::post('delete_feed', 'FeedController@delete_feed');
Route::post('add_feed', 'FeedController@add_feed');
Route::get('get_feeds', 'FeedController@get_feeds');


Route::post('get_single_event', 'EventController@get_single_event');
Route::post('delete_event', 'EventController@delete_event');
Route::post('add_event', 'EventController@add_event');
Route::get('get_events', 'EventController@get_events');













