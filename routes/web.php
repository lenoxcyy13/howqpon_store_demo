<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\LineController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberControllerV2;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/store.html', function (Request $request) {
    $user = (new MemberControllerV2())->getMemberProfileByMemberId($request->session()->get('userId'));
            
    $data = [
        "user" => $user,
    ];

    $data = base64_encode(json_encode($data));
    $data = base64_encode($data);
    return view('welcome',  ["data" => $data]);
});

Route::get('/login.html', function () {
    return view('login');
});

Route::get('/lineLogin', function(Request $request) {
    if(str_starts_with($request->url(), "http://localhost:8003")){
        return redirect("http://127.0.0.1:8003/lineLogin".str_replace($request->url(), '',$request->fullUrl()));
    }

    if(str_starts_with($request->url(), "https://localhost:8003")){
        return redirect("https://127.0.0.1:8003/lineLogin".str_replace($request->url(), '',$request->fullUrl()));
    }

    $url = (new LineController())->getLineLoginPath($request);
    return redirect($url);
});

Route::get('/lineLoginRedirectUrl', function(Request $request) {
    return (new LineController())->executeLineLoginRedirectUrl($request);
});