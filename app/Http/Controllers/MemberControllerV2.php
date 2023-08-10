<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\OrderzController;
use App\Http\Controllers\LineController;

class MemberControllerV2 extends Controller{

    public function getMemberProfile(Request $request){
        $userId = $request["userId"];
        $result = null;

        if($userId != null){
            $user = $this->getMember(new Request(["userId" => $userId, "params" => "userId,name,lineProfile"]));
            if($user != null){
                $pictureUrl = null;
                if($user->lineProfile != null && isset($user->lineProfile->pictureUrl)){
                    $pictureUrl = $user->lineProfile->pictureUrl;
                }
               
                $result = [
                    "name" => $user->name,
                    "pictureUrl" => $pictureUrl,
                ]; 
            }
        }
        return $result;
    }

    public function getMemberProfileByMemberId($userId){
        $result = null;

        if($userId != null){
            $user = $this->getMember(new Request(["userId" => $userId, "params" => "userId,customerId,bindCustomerId,name,lineProfile,lineNotifyAccessToken,project"]));
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

    public function getMemberSourceByMemberId(Request $request){
        $userId = $request->userId;

        $user = $this->getMember(new Request(["userId" => $userId, "params" => "project"]));
        return $user;
    }


    private function getMember(Request $request) {
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
                return $this->tranMemberOutput($results->get(explode(",", $params)))[0];
            }

            return $this->tranMemberOutput($results->get())[0];
        } else {
            return null; 
        }
    }

    public function getMemberRegionCodeByCustomerIdOrBindCustomerId(Request $request) {
        $bindCustomerId = $request->bindCustomerId;
        $customerId = $request->customerId;

        $regionCode = null;
        if($bindCustomerId != null){
            $customerInfo = (new OrderzController())->getCustomerInfo(new Request(["customerId" => $bindCustomerId, "params" => "regionCode"]));
            $regionCode = $customerInfo->regionCode;
        } else if($customerId != null){
            $customer = (new CustomerController())->getCustomerById(new Request(["customerId" => $customerId, "params" => "regionCode"]));
            if($customer != null && isset($customer->regionCode)){
                $regionCode = $customer->regionCode;
            }
        }

        return $regionCode;
    }

    private function tranMemberOutput($members){
        $tmps = [];
        foreach($members as $user){
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

    public function getMemberPageProfileByMemberId($userId){
        if($userId == null){
            return null;
        }

        $user = $this->getMember(new Request([
            "userId" => $userId, 
            "params" => "userId,customerId,bindCustomerId,name,lineProfile,lineNotifyAccessToken,verifyLineNotify,project"
        ]));

        if($user == null){
            return null;
        }

        $user->isEnableLineNoti = $user->lineNotifyAccessToken != null;
        $user->isCreateMemberProfile = $user->customerId != null;

        $regionLineId = null;
        $couponInfo = null;

        if(isset($user->lineProfile->pictureUrl)){
            $user->lineProfile = ["pictureUrl" => $user->lineProfile->pictureUrl];
        } else {
            $user->lineProfile = ["pictureUrl" => null];
        }
        
        if($user->bindCustomerId != null){
            $user->isBindCustomer = true;
        } else {
            $user->isBindCustomer = false;
        }

        unset($user->lineNotifyAccessToken);

        return $user;
    }

    public function isMemberIdExist($userId) {
        try{
            if($userId == null){
                return ["status" => "OK", "result" => false];
            }

            $results = DB::table('line_info');
            $results = $results->where("userId", $userId);
            if($results->count() == 1){
                return ["status" => "OK", "result" => true];
            } else {
                return ["status" => "OK", "result" => false];
            }
        } catch(e){
            return ["status" => "SERVER_ERROR", "result" => false, "message" => e];
        }
    }

    public function updateVerifyLineNotify(Request $request){
        try{
            if($request["verifyLineNotify"] == null){
                return ["status" => "PARAMETER_ERROR", "message" => "參數錯誤"];
            }

            if($request["userId"] == null){
                return ["status" => "PARAMETER_ERROR", "message" => "參數錯誤"];
            }
    
            $tmps = [
                'verifyLineNotify' =>  $request["verifyLineNotify"],
            ];
    
            $result = DB::table('line_info')->where("userId", $request["userId"])->update($tmps);
            if($result == 1){
                return ["status" => "OK"];
            } else {
                return ["status" => "UPDATE_ERROR"];
            }
        } catch(e){
            return ["status" => "SERVER_ERROR", "message" => e];
        }
    }

    public function sendVerfyLineNotifyMsg(Request $request){
        $userId = $request["userId"];
        if($userId == null){
            return;
        }

        $token = $userId;
        $token = base64_encode($token);
        $url = "https://foodz.howqpon.com/verifyLineNotify?token=".$token;

        $msg = "此為驗證LINE Notify連結 => ".$url;
        $this->sendMessage2LineNotify(new Request([
            "userId" => $userId,
            "message" => $msg,
        ]));

        return;
    }

    public function verifyLineNotify(Request $request){
        $token = $request["token"];
        if($token == null){
            return redirect("unknow");
        }

        $userId = base64_decode($token);
        $members = DB::table('line_info')->where("userId", $userId);
        if($members->count() == 1){
            $user = $members->first();
            $result = $members->update(["verifyLineNotify" => 1]);

            if($result == 1){
                $this->sendMessage2LineNotify(new Request([
                    "userId" => $userId,
                    "message" => "驗證LINE Notify成功",
                ]));
            }
        }

        return redirect("user.html");
    }

    public function sendMessage2LineNotify(Request $request){
        $user = null;

        $userId = $request["userId"];
        if($userId != null){
            $members = DB::table('line_info')->where("userId", $userId);
            if($members->count() == 1){
                $user = $members->first();
            }
        }

        $customerId = $request["customerId"];
        if($customerId != null){
            $members = DB::table('line_info')->where("bindCustomerId", $customerId);
            if($members->count() == 1){
                $user = $members->first();
            }
        }

        if($user == null){
            return;
        }

        $message = $request["message"];
        $stickerPackageId = $request["stickerPackageId"];
        $stickerId = $request["stickerId"];

        if(isset($user->lineNotifyAccessToken)){
            (new LineController())->sendLineNotifyMessage(new Request([
                "token" => $user->lineNotifyAccessToken,
                "message" => $message,
                "stickerPackageId" => $stickerPackageId,
                "stickerId" => $stickerId,
            ]));
        }

        return;
    }

    public function updateMemberProject(Request $request) {
        try{
            if($request["userId"] == null){
                return ["status" => "PARAMETER_ERROR", "message" => "參數錯誤"];
            }

            if($request["project"] == null){
                return ["status" => "PARAMETER_ERROR", "message" => "參數錯誤"];
            }

            $userId = $request["userId"];
            $project = $request["project"];

            if(!($project == "fpg")){
                return ["status" => "ERROR"];
            }

            $result = DB::table('line_info')->where("userId", $userId)->update(["project" =>  $project]);
            $user = DB::table('line_info')->where("userId", $userId)->first(explode(",", "userId,customerId,bindCustomerId"));
            
            if(isset($user->customerId)){
                $result = DB::table('customer')->where("customerId", $user->customerId)->update([
                    "project" =>  $project,
                    "location" => "台北市松山區敦化北路201號",
                    "locationMemo" => $request["locationMemo"],
                    "regionCode" => 105,
                ]);
            }

            if(isset($user->bindCustomerId)){
                $result = DB::connection('howqpon_orderz')->table('customer')->where("customerId", $user->bindCustomerId)->update(["project" =>  $project]);
            }

            return ["status" => "OK"];
        } catch(e){
            return ["status" => "SERVER_ERROR", "message" => e];
        }
    }

    function removeTestMember(Request $request){
        $user = DB::table('line_info')->where("userId", $request->userId)->first();
        $customerId = $user->customerId ?? null;
        if($customerId != null){
            DB::table('customer')->where("customerId", $customerId)->delete();
        }
        DB::table('line_info')->where("userId", $request->userId)->delete();
        return "done";
    }
}