<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\UserController;
use Exception;
use Illuminate\Support\Facades\Log;

// use Tymon\JWTAuth\Manager;
// use Tymon\JWTAuth\Token;
use Tymon\JWTAuth\Facades\JWTAuth;

class LineController extends Controller{

    var $login_client_id = "2000644004";
    var $login_client_secret = "578bc1d9e2f17567a0136c3a969b6b70";

    // var $notify_client_id = "F1UkDbjTPHDOJok5qv4SN8";
    // var $notify_client_secret = "9bHDkkkPutfotqGe0RGRtPwyfjAC4EeHHijjVZffQyr";

    var $stateLoginMemberMode = "s1";
    // var $stateLoginJoinGroupOrderMode= "s2";
    // var $stateLoginJoinGroupOrderModeV2= "s21";
    // var $stateLoginCreateOrder = "s3";
    // var $stateLoginCreateGroupOrder = "s4";

    public function getLineLoginPath(Request $request){
        // dd($request);
        $host = $request->getSchemeAndHttpHost();

        $state = [
            "mode" => $this->stateLoginMemberMode,
        ];

        if($request->mode != null){
            if($request->mode == "2"){
                $state = [
                    "mode" => $this->stateLoginJoinGroupOrderMode,
                    "page" => $request["page"],
                    "id" => $request["id"],
                ];
            } else if($request->mode == "3"){
                $state = [
                    "mode" => $this->stateLoginCreateOrder,
                    "token" => $request["token"],
                ];
            } else if($request->mode == "4"){
                $state = [
                    "mode" => $this->stateLoginCreateGroupOrder,
                    "token" => $request["token"],
                ];
            } else if($request->mode == "5"){
                $state = [
                    "mode" => $this->stateLoginJoinGroupOrderModeV2,
                    "id" => $request["id"],
                ];
            }
        }

        if($request->token != null){
            $state["token"] = $request->token;
        }

        $state = base64_encode(json_encode($state));
        return "https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id=".$this->login_client_id."&redirect_uri=".$host."/lineLoginRedirectUrl&state=".$state."&scope=profile%20openid";
    }

    public function getLineToken($code, $redirect_uri){
        $url = 'https://api.line.me/oauth2/v2.1/token';
        $data = [
            "grant_type" => 'authorization_code',
            "code" => $code,
            "redirect_uri" => $redirect_uri,
            "client_id" => $this->login_client_id,
            "client_secret" => $this->login_client_secret,
        ];
        $encodedData = http_build_query($data,'','&');
        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
        $result = curl_exec($ch);
        return $result;
    }

    public function getUserProfile($token){
        $url = "https://api.line.me/v2/profile";
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$token
            ),
        ));
        $result = curl_exec($curl);
        return $result;
    }

    public function executeLineLoginRedirectUrl(Request $request){
        try {
            // dd($request);
            $code = $request->code;
            $state = $request->state;
            $state = json_decode(base64_decode($state));
            // dd($state);
            
            if(isset($state->token)) {
                $tokenParts = explode('.', $state->token);
                if (count($tokenParts) === 3) {
                    // Decode the Header and Payload from base64
                    $header = base64_decode($tokenParts[0]);
                    $payload = base64_decode($tokenParts[1]);
                
                    // Parse the JSON-encoded Header and Payload
                    $decodedHeader = json_decode($header, true);
                    $decodedPayload = json_decode($payload, true);

                } else {
                    echo "Invalid JWT token structure";
                }
                $storeId = intval($decodedPayload['sub']);
                $role = $decodedPayload['role'];
                $noToken = false;
            }
            else {
                $noToken = true;
            }
            
            $lineTokenResult = $this->getLineToken($code, $request->url());
            $lineTokenResult = json_decode($lineTokenResult);
            $isLoginSuccess = false;
            $isNewCreate = false;

            $memberName = null;
            $userId = null;

            $ownStoreNum = null;

            if(isset($lineTokenResult->access_token)){
                $lineUserProfileResult = $this->getUserProfile($lineTokenResult->access_token);
                $lineUserProfileResult = json_decode($lineUserProfileResult);
                // dd($lineUserProfileResult);

                if(isset($lineUserProfileResult->userId)){
                    $memberName = $lineUserProfileResult->displayName;

                    $memberController = new MemberController();
                    $userController = new UserController();
                    $result = $memberController->isMemberExist(new Request(["lineUserId" => $lineUserProfileResult->userId]));
                    // dd($result);

                    if($result["status"] == "OK"){
                        date_default_timezone_set('Asia/Taipei');
                        $currentTime = date('Y-m-d H:i:s');
                        $lineTokenResult->updateTime = $currentTime;
                        $lineUserProfileResult->updateTime = $currentTime;
    
                        if($result["result"] == "false"){
                            $result = $memberController->createMember(new Request([
                                "name" => $memberName,
                                "lineLoginToken" => $lineTokenResult,
                                "lineUserId" => $lineUserProfileResult->userId,
                                "lineProfile" => $lineUserProfileResult,
                                "project" => $state->project ?? null,
                            ]));
                            
                            if($result["status"] == "OK"){
                                // dd($result, $result["userId"], $storeId, $role);
                                $userId = $result["userId"];
                                session(['userId' => $result["userId"]]);
                    
                                $isNewCreate = true;
                                $result = $userController->createRole(new Request([
                                    "userId" => $result["userId"],
                                    "storeId" => $storeId,
                                    "roleName" => $role,
                                ]));
                                $isLoginSuccess = true;
                            }
                        } else if($result["result"] == "true"){
                            $userId = $result["userId"];
                            $result = $memberController->updateMember(new Request([
                                "userId" => $userId,
                                "name" =>  $memberName,
                                "lineLoginToken" => $lineTokenResult,
                                "lineProfile" => $lineUserProfileResult,
                            ]));
                            // dd($result);
    
                            if($result["status"] == "OK"){
                                session(['userId' => $userId]);
                                // dd($userId);
                                $roleCheck = $userController->checkRoles($userId);
                                $ownStoreNum = count($roleCheck);

                                if($roleCheck != null && $noToken){
                                    // dd('no token');
                                    $isLoginSuccess = true;
                                }
                                else {
                                    if ($userController->checkRole($userId, $storeId) != null) {
                                        $isLoginSuccess = true;
                                    }
                                    else {
                                        // dd('no data in role table');
                                        $result = $userController->createRole(new Request([
                                            "userId" => $userId,
                                            "storeId" => $storeId,
                                            "roleName" => $role,
                                        ]));
                                        $ownStoreNum += 1;
                                        $isLoginSuccess = true;
                                    }
                                }
                                // dd($isLoginSuccess);
                            }
                        }
                    }
                }
            }
            // dd($ownStoreNum);
            // dd($storeId);
            if($state->mode == $this->stateLoginMemberMode){
                if($isLoginSuccess){
                    return redirect("profile.html");
                } else {
                    // dd($isLoginSuccess);
                    return redirect("login");
                }
            } else {
                // dd($isLoginSuccess);
                return redirect("login");
            }

        } catch(Exception $e){
            Log::error($e);
            return redirect("login");
        }
    }

    public function getLineNotifyPath(Request $request){
        $host = $request->getSchemeAndHttpHost();
        return "https://notify-bot.line.me/oauth/authorize?response_type=code&scope=notify&client_id=".$this->notify_client_id."&redirect_uri=".$host."/lineNotifyRedirectUrl&state=notify123456";
    }

    public function executeLineNotifyRedirectUrl(Request $request){
        try{
            $code = $request->code;
            $state = $request->state;
    
            $lineNotifyTokenResult = $this->getLineNotifyToken($code, $request->url());
            $lineNotifyTokenResult = json_decode($lineNotifyTokenResult);
            if(isset($lineNotifyTokenResult->status) && isset($lineNotifyTokenResult->access_token) && $lineNotifyTokenResult->status == 200){
                $userId = $request->session()->get('userId');
                $memberController = new MemberController();
                $result = $memberController->isMemberExist(new Request(["userId" => $userId]));
                if($result["status"] == "OK" && $result["result"] == "true"){
                    $userId = $result["userId"];
                    $result = $memberController->updateMember(new Request([
                        "userId" => $userId,
                        "lineNotifyAccessToken" => $lineNotifyTokenResult->access_token,
                    ]));
    
                    if($result["status"] == "OK"){
                        $memberController->sendVerfyLineNotifyMsg(new Request([
                            "userId" => $userId,
                        ]));
                    }
                }
            }
            return redirect("member.html");
        } catch(e){
            Log::error($e);
            return redirect("member.html");
        }
    }

    public function getLineNotifyToken($code, $uri){
        $url = 'https://notify-bot.line.me/oauth/token';
        $data = [
            "grant_type" => 'authorization_code',
            "code" => $code,
            "redirect_uri" => $uri,
            "client_id" => $this->notify_client_id,
            "client_secret" => $this->notify_client_secret,
        ];
        $encodedData = http_build_query($data,'','&');
        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
        $result = curl_exec($ch);
        return $result;
    }

    public function getLineNotifyTokenStatus($token){
        $url = 'https://notify-api.line.me/api/status';
        $headers = [
            'Authorization: Bearer '.$token
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        return $result;
    }

    public function revokeLineNotifyToken(Request $request){
        $token= $request["token"];

        $url = 'https://notify-api.line.me/api/revoke';
        $headers = [
            'Authorization: Bearer '.$token
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        return $result;
    }

    public function sendLineNotifyMessage(Request $request){
        $token = $request["token"];
        if($token == null){
            return;
        }

        $url = 'https://notify-api.line.me/api/notify';

        $data = [];

        if($request["message"]){
            $data["message"] = $request["message"];
        }

        if($request["imageThumbnail"]){
            //ex: "https://upload.wikimedia.org/wikipedia/commons/thumb/a/a5/Instagram_icon.png/240px-Instagram_icon.png"; 
            //Maximum size of 240x240
            $data["imageThumbnail"] = $request["imageThumbnail"]; 
        }

        if($request["imageFullsize"]){
            //ex: "https://upload.wikimedia.org/wikipedia/commons/thumb/a/a5/Instagram_icon.png/240px-Instagram_icon.png"; 
            //Maximum size of 2048×2048
            $data["imageFullsize"] = $request["imageFullsize"]; 
        }

        if($request["stickerPackageId"]){
            $data["stickerPackageId"] = $request["stickerPackageId"]; //ex: 11538
        }

        if($request["stickerId"]){
            $data["stickerId"] = $request["stickerId"]; //ex: 51626494
        }

        $encodedData = http_build_query($data,'','&');
        $headers = [
            'Authorization: Bearer '.$token
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
        $result = curl_exec($ch);
        return $result;
    }
}