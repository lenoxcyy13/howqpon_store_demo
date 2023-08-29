<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\OrderzController;
use App\Http\Controllers\LineController;

class UserController extends Controller {

    private function getUser(Request $request) {
        $results = DB::table('line_info');
        if($request["userId"] != null){
            $results = $results->where("userId", $request['userId']);
        }

        if($request["lineUserId"] != null){
            $results = $results->where("lineUserId", $request['lineUserId']);
        }

        if($results->count() == 1){
            $params = $request->params;
            if($params != null){
                return $this->tranUserOutput($results->get(explode(",", $params)))[0];
            }

            return $this->tranUserOutput($results->get())[0];
        } else {
            return null; 
        }
    }

    public function getUserProfileByUserId($userId){
        $result = null;

        if($userId != null){
            $user = $this->getUser(new Request(["userId" => $userId, "params" => "userId,customerId,bindCustomerId,name,lineProfile,lineNotifyAccessToken,project"]));
            if($user != null){
                $pictureUrl = null;
                if($user->lineProfile != null && isset($user->lineProfile->pictureUrl)){
                    $pictureUrl = $user->lineProfile->pictureUrl;
                }
                
                $result = [
                    "userId" => $user->userId,
                    "customerId" => $user->customerId,
                    "bindCustomerId" => $user->bindCustomerId,
                    "name" => $user->name,
                    "pictureUrl" => $pictureUrl,
                    "project" => $user->project,
                ];
            }
        }
        return $result;
    }

    private function tranUserOutput($users){
        $tmps = [];
        foreach($users as $user){
            $tmp = $user;
            if(isset($user->lineLoginToken)){
                $tmp->lineLoginToken = json_decode($user->lineLoginToken);
            }
            if(isset($user->lineProfile)){
                $tmp->lineProfile = json_decode($user->lineProfile);
            }
            array_push($tmps, $tmp);
        }
        return $tmps;
    }

    public function checkRoles($userId) {

        $result = null;
        $check = DB::table("role")->where("userId", $userId)->exists();

        if($check){
            $result = DB::table("role")->where("userId", $userId)->get();
            $storeRoles = [];
            $i = 0;
            foreach ($result as $item) {
                $storeRoles[$i] = [
                    'storeId' => $item->storeId,
                    'roleId' => $item->roleId,
                ];
                $i++;
            }
        }

        return $storeRoles;
    }

    public function checkRole($userId, $storeId) {
        $result = null;
        $check = DB::table("role")->where("userId", $userId)
                                  ->where("storeId", $storeId)
                                  ->exists();

        if($check){
            $result = DB::table("role")->where("userId", $userId)
                                       ->where("storeId", $storeId)
                                       ->get();
            }
        
        return $result;
    }

    public function createRole(Request $request) {
        try{
            // dd($request);
            
            date_default_timezone_set('Asia/Taipei');
            $createTime = date('Y-m-d H:i:s');

            if($request["roleName"] == "manager") {
                $roleId = 1;
            }
            elseif($request["roleName"] == "clerk")  {
                $roleId = 2;
            }
            else {
                $roleId = 0;
            }

            $tmps = [
                'userId' => $request['userId'],
                'storeId' => $request["storeId"],
                'roleId' => $roleId,
                'roleName' => $request["roleName"],
                'createTime' => $createTime,
            ];
            // dd($tmps);

            $result = DB::table('role')->insert($tmps);
            // dd($result);
            if($result == 1){
                return ["status" => "OK", "userId" => $request['userId']];
            } else {
                return ["status" => "INSERT_ERROR"];
            }
        } catch(e){
            return ["status" => "SERVER_ERROR", "message" => e];
        }
    }


}