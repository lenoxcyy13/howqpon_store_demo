<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use DB;
use Artisan;

class SimpleController extends Controller{

    // 取得所有商家資料
    // parameters: filterIds -> 只需要部分ID的 ex: (1,2,3,4)
    public function getStores(Request $request) {

        $viewExists = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_NAME = 'store_view'");

        if (empty($viewExists)) {
            $stores = DB::statement('CREATE VIEW `howqpon_store`.`store_view` AS SELECT * FROM `howqpon`.`store`');
        }
        $stores = DB::table('store_view');

        $filterIds = $request->filterIds;
        if($filterIds != null){
            $ids = explode(',',$filterIds);
            $isFirst = true;
            foreach($ids as $id){
                if($isFirst){
                    $stores = $stores->where('storeId', $id);
                    $isFirst = false;
                } else {
                    $stores = $stores->orWhere('storeId', $id);
                }
            }
        }

        $filterStoreNos = $request->filterStoreNos;
        if($filterStoreNos != null){
            $storeNos = explode(',',$filterStoreNos);
            $isFirst = true;
            foreach($storeNos as $storeNo){
                if($isFirst){
                    $stores = $stores->where('storeNo', $storeNo);
                    $isFirst = false;
                } else {
                    $stores = $stores->orWhere('storeNo', $storeNo);
                }
            }
        }

        $regionCode = $request->regionCode;
        if($regionCode != null){
            $stores = $stores->where('regionCode', $regionCode);
        }

        $filterServiceRegionCode = $request->filterServiceRegionCode;
        if($filterServiceRegionCode != null){
            $stores = $stores->where('regionAreas', "LIKE", "%\"".$filterServiceRegionCode."\"%");
        }

        $params = $request->params;
        if($params != null){
            $stores = $stores->get(explode(",", $params));
        } else {
            $stores = $stores->get();
        }

        return $this->tranStoresOuput($stores);
    }

    function tranStoresOuput($stores){
        $tmps = [];
        foreach($stores as $store){
            $tmp = $store;
            if(isset($store->meals)){
                $tmp->meals = json_decode($store->meals);
            }
            if(isset($store->extraMeals)){
                $tmp->extraMeals = json_decode($store->extraMeals);
            }
            if(isset($store->uiSettings)){
                $tmp->uiSettings = json_decode($store->uiSettings);
            }
            if(isset($store->addOnStores)){
                $tmp->addOnStores = json_decode($store->addOnStores);
            }
            if(isset($store->marketing)){
                $tmp->marketing = json_decode($store->marketing);
            }
            if(isset($store->regionAreas)){
                $tmp->regionAreas = json_decode($store->regionAreas);
            }
            if(isset($store->mealCategorys)){
                $tmp->mealCategorys = json_decode($store->mealCategorys);
            }
            if(isset($store->mealGroupOptions)){
                $tmp->mealGroupOptions = json_decode($store->mealGroupOptions);
            }
            array_push($tmps, $tmp);
        }
        return $tmps;
    }

}