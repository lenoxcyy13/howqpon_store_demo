sourceData = Base64.decode(data);
sourceData = Base64.decode(sourceData);
sourceData = JSON.parse(sourceData);
sourceData = sourceData[0];

var currentTime = new Date();
if (currentTime.getHours() >= 18) {
    currentTime.addDays(1);
}
inputDate.value = dateTime2YMD(currentTime);

const storeId = sourceData.storeId;
let sourceStore = "";
let storeName = "";

async function refresh() {
    divLoading.innerHTML = "正在載入頁面資料...";

    showElement(divLoading);
    hideElement(divMain);

    divStores.innerHTML = "";
    // divContent.innerHTML = "";
    divNotConfirm.innerHTML="";
    divConfirm.innerHTML="";

    var result = await callGetApi("http://127.0.0.1:8001/api/getOrders?filterDate=" + inputDate.value.replaceAll("-", ""));
    orders = JSON.parse(result.response);
    orders = orders.filter(order => order.totalAmount != 0);

    result = await callGetApi("http://127.0.0.1:8001/api/getStores");
    var stores = JSON.parse(result.response);
    sourceStoreMap = stores.reduce(function(map, obj) {
        map[obj.storeId] = obj;
        return map;
    }, {});

    if (orders.length > 0) {
        showStore(storeId);
    } else {
        divStores.innerHTML = inputDate.value + " 尚無訂單";
    }

    sourceStore = sourceStoreMap[storeId];
    storeName = sourceStore.storeName;
    pStoreName.innerHTML = storeName;

    hideElement(divLoading);
    showElement(divMain);
}

function showStore(storeId) {
    selectStoreId = storeId;
    // pStoreName.innerHTML = storeName;

    var firstRowColor = "#a2dce1";
    if (sourceStore.uiSettings?.colorForParseOrder2Store != null) {
        firstRowColor = sourceStore.uiSettings.colorForParseOrder2Store;
    }

    var tmp = "";

    updateTotal(storeId, storeName);

    var results = storeMap[storeId];

    var sortType = sourceStore.uiSettings?.sortForParseOrder2Store ?? "time";
    results.sort(function(a, b) {
        if (sortType == "time") {
            return new Date(a.order.orderTime) > new Date(b.order.orderTime) ? 1 : -1;
        } else if (sortType == "storeExpectTime") {
            var aT = a.order.storeOrders[0].expectTime;
            var bT = b.order.storeOrders[0].expectTime;
            return aT > bT ? 1 : aT < bT ? -1 : 0;
        } else {
            return 0;
        }
    });

    var totdayStoreTotalAmount = 0;

    var orderDate = "";
    var isFirstOrder = true;
    let tmpIsConfirm = "";
    let tmpNotConfirm = "";
    results.forEach(result => {
        if (result.storeOrder.isStoreConfirm) {
            resultIsConfirm = showEachOrder(isFirstOrder, tmpIsConfirm, result, totdayStoreTotalAmount);
            isFirstOrder = resultIsConfirm.isFirstOrder;
            tmpIsConfirm = resultIsConfirm.tmp;
            totdayStoreTotalAmount += parseInt(tmpIsConfirm.totdayStoreTotalAmount);
        }
        else {
            resultNotConfirm = showEachOrder(isFirstOrder, tmpNotConfirm, result, totdayStoreTotalAmount);
            isFirstOrder = resultNotConfirm.isFirstOrder;
            tmpNotConfirm = resultNotConfirm.tmp;
            totdayStoreTotalAmount += parseInt(resultNotConfirm.totdayStoreTotalAmount);
        }
    });

    addOrdersToDiv(firstRowColor, sourceStore, tmpIsConfirm, divConfirm, totdayStoreTotalAmount);
    addOrdersToDiv(firstRowColor, sourceStore, tmpNotConfirm, divNotConfirm, totdayStoreTotalAmount);

}

function showEachOrder(isFirstOrder, tmp, result, totdayStoreTotalAmount) {

    var order = result.order;
    orderDate = order.orderDate.split("-")[1] + "-" + order.orderDate.split("-")[2];
    var storeOrder = result.storeOrder;
    var meals = storeOrder.meals;

    tmp += `<button class="btn btn-secondary" onclick="openColl('${order.orderNo}')" style="width: 100%">訂單：${order.orderNo} 出餐：${storeOrder.expectTime}</button>`;
    tmp += `<div id="${order.orderNo}" class="collapse-body ${result.storeOrder.isStoreConfirm ? 'isClose' : 'isOpen'}"><table>`;

    var totalAmount = storeOrder.totalAmount;
    var totalQty = storeOrder.totalQty;

    var showMeals = [];
    meals.forEach((meal, mealIndex) => {
        var mealName = combineMealName(meal, true);
        var mealAmount = combineMealAmount(meal);
        var showMealIndex = showMeals.findIndex(showMeal => {
            var showMealName = showMeal.mealName;
            var showMealAmount = Number(showMeal.amount);

            return mealName == showMealName && showMealAmount == mealAmount;
        });

        if (showMealIndex == -1) {
            showMeals.push({
                "mealName": mealName,
                "amount": mealAmount,
                "qty": meal.qty,
                "isMarkUpdate": meal.isMarkUpdate ?? false,
            });
        } else {
            showMeals[showMealIndex].qty += meal.qty;
            if (meal.isMarkUpdate ?? false) {
                showMeals[showMealIndex].isMarkUpdate = true;
            }
        }
    });

    showMeals.sort((a, b) => {
        return a.mealName > b.mealName ? 1 : a.mealName < b.mealName ? -1 : 0;
    })

    showMeals.forEach((meal, mealIndex) => {
        var mealColor = '';
        if ((storeOrder.isCheckOrder ?? false) && (meal.isMarkUpdate ?? false)) {
            mealColor = 'blue';
        }

        var mealName = meal.mealName;
        var mealAmount = meal.amount;

        tmp += createRow(mealIndex+1+'.', mealName, meal.qty, parseNumber2ShowThousand(mealAmount * meal.qty));
    });

    tmp += createRow("總計", "--", totalQty, parseNumber2ShowThousand(totalAmount));

    var feeAmount = storeOrder.feeAmount;
    if (inputCbkShowFeeAmount.checked) {
        tmp += createRow("服務費", "--", "--", "-" + parseNumber2ShowThousand(feeAmount));
    }

    if(storeOrder.storeMealLossAmount != null && storeOrder.storeMealLossAmount > 0){
        tmp += createRow("餐損(店家)", "--", "--", "-" + parseNumber2ShowThousand(storeOrder.storeMealLossAmount), "#FF8153");
    }

    if(storeOrder.storeFreightAmount != null && storeOrder.storeFreightAmount > 0){
        tmp += createRow("運費(店家)", "--", "--", "-" + parseNumber2ShowThousand(storeOrder.storeFreightAmount));
    }

    var storePayableAmount = (totalAmount - feeAmount - (storeOrder.storeMealLossAmount ?? 0) - (storeOrder.storeFreightAmount ?? 0));
    tmp += createRow("應付帳款", "--", "--", parseNumber2ShowThousand(storePayableAmount));
    totdayStoreTotalAmount += storePayableAmount;

    tmp += createRow("備註", storeOrder.memo ?? "", "", "");
    tmp += createRow("餐具", order.isNeedTableware ? "是" : "否", "", "");
    tmp += createRow("收據", order.isNeedReceipt ? `是${order.taxIdNumber != null ? `(統編${order.taxIdNumber})` : ""}` : "否", "", "");
    isFirstOrder = false;

    tmp += `<tr>
        <td colspan="7" style="text-align:center; color:red; font-weight: 600">餐點請務必先裝入塑膠袋，再裝入保溫袋</td>
        </tr></table>`;
    tmp += (storeOrder.isStoreConfirm ?? false) ?
    `<button class="btn btn-secondary btn-bold" onclick="updateStoreOrderConfirm('${order.orderId}', '${storeId}', false)" style="margin: 10px">取消確認</button></div>` :
    `<button class="btn btn-failed btn-bold" onclick="updateStoreOrderConfirm('${order.orderId}', '${storeId}', true)" style="margin: 10px">
        <p style="margin-bottom: 0px">${order.orderNo} ${storeOrder.expectTime}出餐</p>
        <p style="margin-bottom: 0px">共${totalQty}份 點我確認</p></button></div>`;

    return {'isFirstOrder': isFirstOrder, 'tmp': tmp, 'totdayStoreTotalAmount': totdayStoreTotalAmount};
}

function addOrdersToDiv(firstRowColor, sourceStore, tmp, div, totdayStoreTotalAmount) {
    var contentHTML =
        `<a href="javascript:void(0)" onclick="tableContentCapture()">下載成圖檔</a>`;

    var info = sourceStore.uiSettings?.pasrseOrder2StoreInfo;
    if(info != null){
        contentHTML += `<div style="color:red;font-size:20px; border: 1px solid red;"><font style="font-weight:bold;">叫餐提醒說明：</font><br>${info}</div>`;
    }
    contentHTML += "<br>";
    contentHTML += `<div id='tableContent'>${tmp}</div>`;

    div.innerHTML = contentHTML;
}

function updateTotal(storeId, storeName) {
    storeMap = {};
    orders.forEach(order => {
        order.storeOrders.forEach(storeOrder => {

            if (storeMap[storeOrder.storeId] == null) {
                storeMap[storeOrder.storeId] = [];
            }

            storeMap[storeOrder.storeId].push({
                "order": order,
                "storeOrder": storeOrder
            });
        });
    });
    // var storeIds = Object.keys(storeMap);
    var caculateResult = caculateUpdateConunt(storeId);

    var fontColor = "red";
    if (caculateResult.isAllCheck) {
        fontColor = "green";
    }
    var checkColor = "";
    if (!caculateResult.isAllCheck) {
        checkColor = "red";
    }

    var confirmColor = "";
    if (!caculateResult.isAllConfirm) {
        confirmColor = "red";
    }
    tmp2 =
        `<table>
        <tr><td>店名</td><td>總數</td><td>已叫餐</td><td>店家已確認</td></tr>
        <tr><td>${storeName}</td>
        <td>${caculateResult.allCount}</td>
        <td style="background-color:${checkColor}">${caculateResult.isCheckCount}</td>
        <td style="background-color:${confirmColor}">${caculateResult.isConfirmCount}</td>
        </tr></table>`;
    // divTotal.innerHTML = tmp2;
    btn_not.innerHTML = "待確認 "+caculateResult.notConfirmCount;
    btn_confirm.innerHTML = "已確認 "+caculateResult.isConfirmCount;


}

function createRow(r1, r2, r3, r4, bgcolor, mealNameColor) {
    if (r1 == "收據" && (r2.indexOf("是") == 0)) {
        return `<tr bgcolor="${bgcolor}">
            <td class="td_no">${r1}</td>
            <td class="td_meal" style="color:red">${r2}</td>
            <td class="td_qty">${r3}</td>
            <td class="td_amount">${r4}</td>
            </tr>`;
    }

    return `<tr style="background-color: ${bgcolor};">
        <td class="td_no" onclick="onClickTd(this)">${r1}</td>
        <td class="td_meal" onclick="onClickTd(this)" style="color:${mealNameColor}">${r2}</td>
        <td class="td_qty" onclick="onClickTd(this)">${r3}</td>
        <td class="td_amount" onclick="onClickTd(this)">${r4}</td>
        </tr>`;
}

async function updateStoreOrderConfirm(orderId, storeId, isStoreConfirm) {
    var order;
    orders.forEach(element => {
        if (element.orderId == orderId) {
            order = element;
        }
    });

    var storeOrders = JSON.parse(JSON.stringify(order.storeOrders));
    storeOrders.forEach(storeOrder => {
        if (storeOrder.storeId == storeId) {
            storeOrder.isStoreConfirm = isStoreConfirm;
        }
    });

    divLoading.innerHTML = "正在更新資料...";
    showElement(divLoading);
    hideElement(divMain);

    var result = await callPostApi("http://127.0.0.1:8001/api/updateStoreOrderConfirm", {
        "datas": [{
            "orderId": orderId,
            "isAllStoreConfirm": false,
            "isStoreConfirm": isStoreConfirm,
            "storeIds": [storeId],
        }],
    });

    result = JSON.parse(result.response);
    if (result.status == "OK") {
        orders.forEach(element => {
            if (element.orderId == orderId) {
                order.storeOrders = storeOrders;
            }
        });
        // updateContentUi();
        showStore(storeId);
        // setInterval(myCallback, 100);
    }

    hideElement(divLoading);
    showElement(divMain);
}

function tableContentCapture() {
    var hides = document.getElementsByClassName("btn-bold");
    hides += document.getElementsByTagName("a");
    console.log(hides);
    console.log(hide2);

    for (let hide of hides) {
        hideElement(hide);
    }

    var store = sourceStoreMap[selectStoreId];
    var fileName = inputDate.value.replaceAll("-", "") + "_" + store.storeName;

    html2canvas(document.querySelector("#divContent")).then(function(canvas) {
        a = document.createElement('a');
        a.href = canvas.toDataURL("image/jpeg", 0.92).replace("image/jpeg", "image/octet-stream");
        a.download = fileName + '.jpg';
        a.click();

        for (let hide of hides) {
            showElement(hide);
        }
    });
}

function caculateUpdateConunt(storeId) {
    let results = storeMap[storeId];
    let allCount = results.length;
    let isCheck = results.filter(result => (result.storeOrder.isCheckOrder ?? false) && (result.storeOrder.updateCheckOrderText == null));
    let isCheckCount = (isCheck).length;
    let isConfirm = results.filter(result => (result.storeOrder.isStoreConfirm ?? false));
    let isConfirmCount = (isConfirm).length;
    return {
        "allCount": allCount,
        "isCheckCount": isCheckCount,
        "isConfirmCount": isConfirmCount,
        "notConfirmCount": allCount-isConfirmCount,
        "isAllCheck": allCount == isCheckCount,
        "isAllConfirm": allCount == isConfirmCount,
        "isCheck": isCheck,
        "isConfirm": isConfirm
    };
}


refresh();