<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\LineController;

class MemberController extends Controller{

    public function getLoginMemberId(Request $request){
        return $request->session()->get('userId');
    }
    
    public function getMemberProfile(Request $request){
        $userId = $request->session()->get('userId');
        $result = null;
        if($userId != null){
            $member = (new MemberController())->getMember(new Request(["userId" => $userId, "params" => "userId,bindCustomerId,name,lineProfile,lineNotifyAccessToken"]));
            if($member != null && $member->lineNotifyAccessToken != null && $member->bindCustomerId != null){
                $pictureUrl = null;
                if($member->lineProfile != null && isset($member->lineProfile->pictureUrl)){
                    $pictureUrl = $member->lineProfile->pictureUrl;
                }
                
                $result = [
                    "userId" => $member->userId,
                    "bindCustomerId" => $member->bindCustomerId,
                    "name" => $member->name,
                    "pictureUrl" => $pictureUrl,
                ];
            }
        }

        return $result;
    }

    public function createMember(Request $request) {
        try{
            if($request["name"] == null){
                return ["status" => "PARAMETER_ERROR", "message" => "未輸入姓名"];
            }

            if($request["lineUserId"] == null){
                return ["status" => "PARAMETER_ERROR", "message" => "參數錯誤"];
            }

            if($request["lineProfile"] == null){
                return ["status" => "PARAMETER_ERROR", "message" => "參數錯誤"];
            }

            $userId = null;
            while(1){
                // $str = substr(strtoupper(md5(uniqid(rand(), true))), 0, 12);
                $str = substr(strtoupper(md5(uniqid(rand(), true))), 0, 7).str_pad(rand(0, 99999), 5,'0',STR_PAD_LEFT);
                $userId = "S_".$str;
                $results = DB::table('line_info')->where("userId", $userId);
                if($results->count() == 0){
                    break;
                }
            }

            $lineLoginToken = null;
            if($request["lineLoginToken"] != null){
                $lineLoginToken = json_encode($request["lineLoginToken"]);
            }

            $lineProfile = null;
            if($request["lineProfile"] != null){
                $lineProfile = json_encode($request["lineProfile"]);
            }

            date_default_timezone_set('Asia/Taipei');
            $createTime = date('Y-m-d H:i:s');

            $tmps = [
                'userId' => $userId,
                'name' => $request["name"],
                'lineLoginToken' => $lineLoginToken,
                'lineUserId' => $request["lineUserId"],
                'lineProfile' => $lineProfile,
                'createTime' => $createTime,
                'project' => $request["project"],
            ];

            $result = DB::table('line_info')->insert($tmps);
            if($result == 1){
                return ["status" => "OK", "userId" => $userId];
            } else {
                return ["status" => "INSERT_ERROR"];
            }
        } catch(e){
            return ["status" => "SERVER_ERROR", "message" => e];
        }
    }

    public function updateMember(Request $request) {
        try{

            $tmps = [];
            if($request["name"] != null){
                $tmps["name"] = $request["name"];
            }

            if($request["lineLoginToken"] != null){
                $tmps["lineLoginToken"] = json_encode($request["lineLoginToken"]);
            }

            if($request["lineProfile"] != null){
                $tmps["lineProfile"] = json_encode($request["lineProfile"]);
            }

            if($request["lineNotifyAccessToken"] != null){
                $tmps["lineNotifyAccessToken"] = $request["lineNotifyAccessToken"];
            }

            $result = DB::table('line_info')->where("userId", $request["userId"])->update($tmps);
            if($result == 1){
                return ["status" => "OK"];
            } else {
                return ["status" => "INSERT_ERROR"];
            }
        } catch(e){
            return ["status" => "SERVER_ERROR", "message" => e];
        }
    }

    public function getMember(Request $request) {
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

    public function isMemberExist(Request $request) {
        try{
            $isCallWhere = false;
            $results = DB::table('line_info');
            if($request["userId"] != null){
                $results = $results->where("userId", $request['userId']);
                $isCallWhere = true;
            }

            if($request["lineUserId"] != null){
                $results = $results->where("lineUserId", $request['lineUserId']);
                $isCallWhere = true;
            }

            if($results->count() == 1 && $isCallWhere){
                return ["status" => "OK", "result" => "true", "userId" => $results->first()->userId];
            } else {
                return ["status" => "OK", "result" => "false"];
            }
        }catch(e){
            return ["status" => "SERVER_ERROR", "message" => e];
        }
    }

    function tranMemberOutput($members){
        $tmps = [];
        foreach($members as $member){
            $tmp = $member;
            if(isset($member->lineLoginToken)){
                $tmp->lineLoginToken = json_decode($member->lineLoginToken);
            }
            if(isset($member->lineProfile)){
                $tmp->lineProfile = json_decode($member->lineProfile);
            }
            array_push($tmps, $tmp);
        }
        return $tmps;
    }

    public function createMemberProfile(Request $request) {
        try{
            if($request["name"] == null){
                return ["status" => "PARAMETER_ERROR", "message" => "未輸入姓名"];
            }

            if($request["companyName"] == null){
                return ["status" => "PARAMETER_ERROR", "message" => "未輸入公司名稱"];
            }

            if($request["lunchBreakDatePart"] == null){
                return ["status" => "PARAMETER_ERROR", "message" => "未輸入午休時段"];
            }

            if($request["deliveryType"] == null){
                return ["status" => "PARAMETER_ERROR", "message" => "未輸入送餐方式"];
            }

            if($request["regionCode"] == null){
                return ["status" => "PARAMETER_ERROR", "message" => "未選擇行政區"];
            }

            if($request["location"] == null){
                return ["status" => "PARAMETER_ERROR", "message" => "未輸入地址"];
            }

            if($request["phones"] == null){
                return ["status" => "PARAMETER_ERROR", "message" => "未輸入電話"];
            }

            if($request["paymentType"] == null){
                return ["status" => "PARAMETER_ERROR", "message" => "未輸入付款方式"];
            }

            $userId = $request->session()->get('userId');
            if($userId == null){
                return ["status" => "ERROR_NOT_FOUND_MEMBER", "message" => "未找到會員編號"];
            }

            $members = DB::table('line_info')->where("userId", $userId);
            if($members->count() != 1){
                return ["status" => "ERROR_NOT_FOUND_MEMBER", "message" => "未找到會員編號"];
            }

            $customerId = null;
            while(1){
                $str = substr(strtoupper(md5(uniqid(rand(), true))), 0, 12);
                $customerId = "US_".$str;
                $results = DB::table('customer')->where("customerId", $customerId);
                if($results->count() == 0){
                    break;
                }
            }

            date_default_timezone_set('Asia/Taipei');
            $createTime = date('Y-m-d H:i:s');

            $tmps = [
                'customerId' => $customerId,
                'name' => $request["name"],
                'companyName' => $request["companyName"],
                'companyDept' => $request["companyDept"],
                'lunchBreakDatePart' => $request["lunchBreakDatePart"],
                'deliveryType' => $request["deliveryType"],
                'regionCode' => $request["regionCode"],
                'location' => $request["location"],
                'locationMemo' => $request["locationMemo"],
                'phones' => json_encode($request["phones"]),
                'isNeedTableware' => $request->isNeedTableware ? 1 : 0,
                'isNeedReceipt' => $request->isNeedReceipt ? 1 : 0,
                'taxIdNumber' => $request["taxIdNumber"],
                'memo' => $request["memo"],
                'paymentType' => $request["paymentType"],
                'createTime' => $createTime,
                'project' => $request["project"],
                'email' => $request["email"],
            ];

            $result = DB::table('customer')->insert($tmps);
            if($result == 1){
                $result = $members->update(["customerId" => $customerId]);
                if($result == 1){
                    return ["status" => "OK"];
                } else {
                    return ["status" => "MEMBER_UPDATE_ERROR"];
                }
            } else {
                return ["status" => "INSERT_ERROR"];
            }
        } catch(e){
            return ["status" => "SERVER_ERROR", "message" => e];
        }
    }

    public function enableLineNotify(Request $request) {
        try{
            if($request["userId"] == null){
                return ["status" => "PARAMETER_ERROR", "message" => "參數錯誤"];
            }
            $results = DB::table('line_info')->where("userId", $request["userId"]);
            if($results->count() != 1){
                return ["status" => "ENABLE_ERROR"];
            }

            $member = $results->first;
            if(isset($member->lineNotifyAccessToken)){
                $token = $member->lineNotifyAccessToken;
                (new LineController())->getLineNotifyToken($token);
                
            }
            return ["status" => "OK"];
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
            $member = $members->first();
            $result = $members->update(["verifyLineNotify" => 1]);

            if($result == 1){
                $this->sendMessage2LineNotify(new Request([
                    "userId" => $userId,
                    "message" => "驗證LINE Notify成功",
                ]));
            }
        }

        return redirect("member.html");
    }

    public function sendTestMessage2LineNotify(Request $request){
        $userId = $request["userId"];
        if($userId == null){
            return;
        }

        return $this->sendMessage2LineNotify(new Request([
            "userId" => $userId,
            "message" => "測試訊息",
            "stickerPackageId" => 11538,
            "stickerId" => 51626494,
        ]));
    }

    public function sendMessage2LineNotify(Request $request){
        $member = null;

        $userId = $request["userId"];
        if($userId != null){
            $members = DB::table('line_info')->where("userId", $userId);
            if($members->count() == 1){
                $member = $members->first();
            }
        }

        $customerId = $request["customerId"];
        if($customerId != null){
            $members = DB::table('line_info')->where("bindCustomerId", $customerId);
            if($members->count() == 1){
                $member = $members->first();
            }
        }

        if($member == null){
            return;
        }

        $message = $request["message"];
        $stickerPackageId = $request["stickerPackageId"];
        $stickerId = $request["stickerId"];

        if(isset($member->lineNotifyAccessToken)){
            (new LineController())->sendLineNotifyMessage(new Request([
                "token" => $member->lineNotifyAccessToken,
                "message" => $message,
                "stickerPackageId" => $stickerPackageId,
                "stickerId" => $stickerId,
            ]));
        }

        return;
    }
}