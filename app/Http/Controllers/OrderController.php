<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use DB;

class OrderController extends Controller{

    // 取得所有訂單
    // parameters: filterIds -> 只需要部分ID的 ex: (1,2,3,4)
    //             filterDate -> 只需要指定日期
    //             filterDates -> 只需要指定多筆日期
    public function getOrders(Request $request) {
        // if(config('auth.enable_auth_api') && $request->header('AUTH_API_TOKEN') != config('auth.AUTH_API_TOKEN')){
        //     return [];
        // }

        $orders = DB::table('orders_view');
        $filterIds = $request->filterIds;
        if($filterIds != null){
            $ids = explode(',',$filterIds);
            $isFirst = true;
            foreach($ids as $id){
                if($isFirst){
                    $orders = $orders->where('orderId', $id);
                    $isFirst = false;
                } else {
                    $orders = $orders->orWhere('orderId', $id);
                }
            }
        }

        $filterDate = $request->filterDate;
        if($filterDate != null){
            if(str_contains($filterDate, '-')){
                $orders = $orders->where('orderDate', $filterDate);
            } else {
                $orders = $orders->where('orderId', 'like', $filterDate.'%');
            }
        }

        $filterRegionCode = $request->filterRegionCode;
        if($filterRegionCode != null){
            $orders = $orders->where('regionCode', $filterRegionCode);
        }

        $filterDates = $request->filterDates;
        if($filterDates != null){
            $dates = explode(',',$filterDates);
            $isFirst = true;
            foreach($dates as $date){
                if($isFirst){
                    $orders = $orders->where('orderId', 'like', $date.'%');
                    $isFirst = false;
                } else {
                    $orders = $orders->orWhere('orderId', 'like', $date.'%');
                }
            }
        }

        $filterCustomerId = $request->filterCustomerId;
        if($filterCustomerId != null){
            $orders = $orders->where('customerId', $filterCustomerId);
        }

        $startDate = $request->startDate;
        $endDate = $request->endDate;
        if($startDate != null && $endDate != null){
            $from = date($startDate);
            $to = date($endDate);
            $orders = $orders->whereBetween('orderDate', [$from, $to]);
        }

        $filterStoreId = $request->filterStoreId;
        if($filterStoreId != null){
            $orders = $orders->where('storeOrders', 'like', '%"storeId":'.$filterStoreId.'%');
        }

        $params = $request->params;
        if($params != null){
            return $this->tranOrderOuput($orders->get(explode(",", $params)));
        }

        return $this->tranOrderOuput($orders->get());
    }

    function tranOrderOuput($orders){
        $tmps = [];
        foreach($orders as $order){
            $tmp = $order;
            if(isset($order->orderId)){
                $tmp->orderId = (String) $order->orderId;
            }
            if(isset($order->customerLocation)){
                $tmp->customerLocation = json_decode($order->customerLocation);
            }
            if(isset($order->storeOrders)){
                $tmp->storeOrders = json_decode($order->storeOrders);
            }
            if(isset($order->discounts)){
                $tmp->discounts = json_decode($order->discounts);
            }
            array_push($tmps, $tmp);
        }
        return $tmps;
    }
    
    public function updateStoreOrderConfirm(Request $request) {
        try{
            $datas = $request["datas"];
            $count = count($datas);
            $successCount = 0;
    
            foreach($datas as $data){

                $orderId = $data['orderId'];
                $isStoreConfirm = $data['isStoreConfirm'];
                // $storeIds = $data['storeIds'];
                // $isAllStoreConfirm = $data['isAllStoreConfirm'];
                $currentTime = date('Y-m-d H:i:s');

                $order = DB::table('order_confirm')->where('orderId', (String) $orderId);
                if(isset($isStoreConfirm)){
                    
                    $tmps = [
                        'confirmStatus' => $isStoreConfirm,
                        'confirmTime' => $currentTime
                    ];
                    $result = $order->update($tmps);
                    if($result == 1){
                        $successCount += 1;
                    }
                }
            }
    
            if($count == $successCount){
                return ["status" => "OK"];
            } 
        } catch(e){
            return ["status" => "ERROR"]; 
        }  
    
        return ["status" => "UNKNOW_ERROR"]; 
    }

    public function checkStoreOrderConfirm(Request $request) {
        $result = null;
        $filterDate = $request->filterDate;
        $orders = DB::table('order_confirm')->where('orderId', 'like', $filterDate.'%')->get();
        if(!$orders->isEmpty()){
            $result = $orders;
        }
        return ['confirmStatus' => $result];
    }
}