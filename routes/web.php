<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\LineController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberControllerV2;

use Tymon\JWTAuth\Facades\JWTAuth;

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

Route::get('/profile.html', function (Request $request) {
    $userId = $request->session()->get('userId');
    $user = (new UserController())->getUserProfileByUserId($userId);

    $roles = (new UserController())->checkRoles($userId);
    $data = [
        "name" => $user['name'],
        "pictureUrl" => $user['pictureUrl'],
        "roles" => $roles,
    ];
    $data = base64_encode(json_encode($data));
    $data = base64_encode($data);
    return view('profile', ['data' => $data]);
});

Route::get('/logout', function(Request $request) {
    $request->session()->forget('userId');
    return redirect("login");
});

Route::get('/login', function(Request $request) {
    $token = $request -> token;
    return view('login', ['token' => $token]);
    // echo '<script>alert("請向好客萌索取店家登入連結")</script>';
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

Route::get('/store', function (Request $request) {
    $userId = $request->session()->get('userId');
    $storeId = $request->storeId;
    $storeId = preg_replace('/\.html$/', '', $storeId);

    $user = (new UserController())->getUserProfileByUserId($userId);
    if($user == null) {
        return redirect('login');
    }
    else {
        $data = (new UserController())->checkRole($userId, $storeId);
    }
    
    if ($data != null) {
        $data = base64_encode(json_encode($data));
        $data = base64_encode($data);
        return view('welcome',  ["data" => $data]);
    }
    else {
        return redirect('login');
    }
    
});