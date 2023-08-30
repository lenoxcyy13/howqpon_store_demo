sourceData = Base64.decode(data);
sourceData = Base64.decode(sourceData);
sourceData = JSON.parse(sourceData);
sourceData = sourceData[0];

let currentTime = new Date();
if (currentTime.getHours() >= 18) {
    currentTime.addDays(1);
}
inputDate.value = dateTime2YMD(currentTime);

const storeId = sourceData.storeId;
let sourceStore = "";
let storeName = "";
let storeMap = {};

let themeColor = "";
let headContent = "";

async function refresh() {
    divLoading.innerHTML = "正在載入頁面資料...";

    showElement(divLoading);
    hideElement(divMain);

    divStores.innerHTML = "";
    divNotConfirm.innerHTML="";
    divConfirm.innerHTML="";
    btn_not.innerHTML = "待確認"
    btn_confirm.innerHTML = "已確認";

    let result = await callGetApi("http://127.0.0.1:8001/api/getOrders?filterDate=" + inputDate.value.replaceAll("-", ""));
    orders = JSON.parse(result.response);
    orders = orders.filter(order => order.totalAmount != 0);

    result = await callGetApi("http://127.0.0.1:8001/api/getStores");
    let stores = JSON.parse(result.response);
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

    let results = storeMap[storeId];
    if (results) {
        let sortType = sourceStore.uiSettings?.sortForParseOrder2Store ?? "time";
        results.sort(function(a, b) {
            if (sortType == "time") {
                return new Date(a.order.orderTime) > new Date(b.order.orderTime) ? 1 : -1;
            } 
            else if (sortType == "storeExpectTime") {
                let aT = a.order.storeOrders[0].expectTime;
                let bT = b.order.storeOrders[0].expectTime;
                return aT > bT ? 1 : aT < bT ? -1 : 0;
            } 
            else {
                return 0;
            }
        });

        updateTotal(storeId, storeName);
        let totdayStoreTotalAmount = 0;
        let isFirstOrder = true;
        let tmpIsConfirm = "";
        let tmpNotConfirm = "";

        results.forEach(result => {
            if (result.storeOrder.isStoreConfirm) {
                if (result.order.sourceType == 1) {
                    themeColor = 'var(--grey-900)';
                    headContent = '店家轉單';
                }
                else {
                    themeColor = 'var(--green-500)';
                    headContent = '訂單編號';
                }
                resultIsConfirm = showEachOrder(isFirstOrder, tmpIsConfirm, result, totdayStoreTotalAmount);
                isFirstOrder = resultIsConfirm.isFirstOrder;
                tmpIsConfirm = resultIsConfirm.tmp;
                totdayStoreTotalAmount += parseInt(tmpIsConfirm.totdayStoreTotalAmount);
            }
            else {
                if (result.order.sourceType == 1) {
                    themeColor = 'var(--grey-900)';
                    headContent = '店家轉單';
                }
                else {
                    themeColor = 'var(--red-500)';
                headContent = '全新訂單';
                }

                resultNotConfirm = showEachOrder(isFirstOrder, tmpNotConfirm, result, totdayStoreTotalAmount);
                isFirstOrder = resultNotConfirm.isFirstOrder;
                tmpNotConfirm = resultNotConfirm.tmp;
                totdayStoreTotalAmount += parseInt(resultNotConfirm.totdayStoreTotalAmount);
            }
        });

        addOrdersToDiv(sourceStore, tmpIsConfirm, divConfirm, totdayStoreTotalAmount);
        addOrdersToDiv(sourceStore, tmpNotConfirm, divNotConfirm, totdayStoreTotalAmount);
    }
}

function showEachOrder(isFirstOrder, tmp, result, totdayStoreTotalAmount) {

    let order = result.order;
    let orderDate = order.orderDate.split("-")[1] + "-" + order.orderDate.split("-")[2];
    let storeOrder = result.storeOrder;
    let tmpMeal = '';
    let cTotalQty = '';
    let meals = storeOrder.meals;

    let totalAmount = storeOrder.totalAmount;
    let totalQty = storeOrder.totalQty;

    let showMeals = [];
    meals.forEach((meal, mealIndex) => {
        let mealName = combineMealName(meal, true);
        let mealAmount = combineMealAmount(meal);
        let showMealIndex = showMeals.findIndex(showMeal => {
            let showMealName = showMeal.mealName;
            let showMealAmount = Number(showMeal.amount);
            return mealName == showMealName && showMealAmount == mealAmount;
        });

        if (showMealIndex == -1) {
            showMeals.push({
                "mealName": mealName,
                "amount": mealAmount,
                "qty": meal.qty,
                "isMarkUpdate": meal.isMarkUpdate ?? false,
            });
        } 
        else {
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
        let mealColor = '';
        if ((storeOrder.isCheckOrder ?? false) && (meal.isMarkUpdate ?? false)) {
            mealColor = 'blue';
            themeColor = mealColor;
            headContent = '原單更新';
            cTotalQty = `改為${totalQty}份`;
        }

        let mealName = meal.mealName;
        let mealAmount = meal.amount;

        tmpMeal += createRow('', mealIndex+1+'.&nbsp'+mealName, meal.qty, parseNumber2ShowThousand(mealAmount * meal.qty), mealColor);
    });

    tmp += `<button class="btn btn-secondary collapse-head" style="color: ${themeColor} !important;" onclick="openColl('${order.orderNo}')">
                <span>● ${headContent}：${order.orderNo}</span>
                <span>${cTotalQty}</span>
                <span>出餐：${storeOrder.expectTime}</span>
            </button>`;
    tmp += `<div id="${order.orderNo}" class="collapse-body ${result.storeOrder.isStoreConfirm ? 'isClose' : 'isOpen'}">
                <table id="${order.orderNo}_table" style="border-color: ${themeColor}; border-width: 2px">`;
    
    tmp += tmpMeal;

    tmp += createRow("總計", "--", totalQty, parseNumber2ShowThousand(totalAmount));

    let feeAmount = storeOrder.feeAmount;
    if (inputCbkShowFeeAmount.checked) {
        tmp += createRow("服務費", "--", "--", "-" + parseNumber2ShowThousand(feeAmount));
    }

    if(storeOrder.storeMealLossAmount != null && storeOrder.storeMealLossAmount > 0){
        tmp += createRow("餐損(店家)", "--", "--", "-" + parseNumber2ShowThousand(storeOrder.storeMealLossAmount), "#FF8153");
    }

    if(storeOrder.storeFreightAmount != null && storeOrder.storeFreightAmount > 0){
        tmp += createRow("運費(店家)", "--", "--", "-" + parseNumber2ShowThousand(storeOrder.storeFreightAmount));
    }

    let storePayableAmount = (totalAmount - feeAmount - (storeOrder.storeMealLossAmount ?? 0) - (storeOrder.storeFreightAmount ?? 0));
    tmp += createRow("應付帳款", "--", "--", parseNumber2ShowThousand(storePayableAmount));
    totdayStoreTotalAmount += storePayableAmount;

    tmp += createRow("備註", storeOrder.memo ?? "", "", "");
    tmp += createRow("餐具", order.isNeedTableware ? "是" : "否", "", "");
    tmp += createRow("收據", order.isNeedReceipt ? `是${order.taxIdNumber != null ? `(統編${order.taxIdNumber})` : ""}` : "否", "", "");
    isFirstOrder = false;

    tmp += `<tr><td colspan="7" style="text-align:center; color:red; font-weight: 600">收據發票請開【原價金額】不要扣除服務費</td></tr>
            <tr><td colspan="7" style="text-align:center; color:red; font-weight: 600">餐點請務必先裝入塑膠袋，再裝入保溫袋</td></tr></table>`;

    tmp += (storeOrder.isStoreConfirm ?? false) ?
    `<div style="margin: 10px">
        <button class="btn btn-secondary btn-bold" onclick="updateStoreOrderConfirm('${order.orderId}', '${storeId}', false)">取消確認</button></div></div>` :
    `<div style="margin: 10px">
        <button class="btn btn-failed btn-bold" onclick="updateStoreOrderConfirm('${order.orderId}', '${storeId}', true)">
        <div style="display: flex;">
            <h3 style="margin-bottom: 0px">${order.orderNo}</h3>
            <h3 style="margin-left: 30px">${storeOrder.expectTime}出餐</h3>
        </div>
        <h3 style="margin-bottom: 0px">共${totalQty}份&nbsp; 點我確認</h3></button></div></div>`;
``
    return {'isFirstOrder': isFirstOrder, 'tmp': tmp, 'totdayStoreTotalAmount': totdayStoreTotalAmount};
}

function addOrdersToDiv(sourceStore, tmp, div, totdayStoreTotalAmount) {
    let contentHTML =
        `<div class="dwld"><button class='btn btn-secondary btn-minimal' onclick="clickColl(this)">►</button>`;

    let info = sourceStore.uiSettings?.pasrseOrder2StoreInfo;
    if(info != null){
        contentHTML += `<div style="color:red;font-size:20px; border: 1px solid red;"><font style="font-weight:bold;">叫餐提醒說明：</font><br>${info}</div>`;
    }
    contentHTML += `<a href="javascript:void(0)" onclick="tableContentCapture()">下載成圖檔</a></div>`;
    contentHTML += `<div id='tableContent'>${tmp}</div>`;

    div.innerHTML = contentHTML;
}

function updateTotal(storeId, storeName) {

    let caculateResult = caculateUpdateConunt(storeId);

    let fontColor = "red";
    if (caculateResult.isAllCheck) {
        fontColor = "green";
    }
    let checkColor = "";
    if (!caculateResult.isAllCheck) {
        checkColor = "red";
    }

    let confirmColor = "";
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

    btn_not.innerHTML = "待確認 "+caculateResult.notConfirmCount;
    btn_confirm.innerHTML = "已確認 "+caculateResult.isConfirmCount;

}

function createRow(r1, r2, r3, r4, mealNameColor) {
    if (r1 == "收據" && (r2.indexOf("是") == 0)) {
        return `<tr>
            <td class="td_no">${r1}</td>
            <td class="td_meal" style="color:red">${r2}</td>
            <td class="td_qty">${r3}</td>
            <td class="td_amount">${r4}</td>
            </tr>`;
    }

    return `<tr>
        <td class="td_no">${r1}</td>
        <td class="td_meal" style="display:flex; color:${mealNameColor}">${r2}</td>
        <td class="td_qty">${r3}</td>
        <td class="td_amount">${r4}</td>
        </tr>`;
}

async function updateStoreOrderConfirm(orderId, storeId, isStoreConfirm) {
    let order;
    orders.forEach(element => {
        if (element.orderId == orderId) {
            order = element;
        }
    });

    let storeOrders = JSON.parse(JSON.stringify(order.storeOrders));
    storeOrders.forEach(storeOrder => {
        if (storeOrder.storeId == storeId) {
            storeOrder.isStoreConfirm = isStoreConfirm;
        }
    });

    divLoading.innerHTML = "正在更新資料...";
    showElement(divLoading);
    hideElement(divMain);

    let result = await callPostApi("http://127.0.0.1:8001/api/updateStoreOrderConfirm", {
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
        showStore(storeId);
        myInterval = setInterval(myCallback, 50);
    }

    hideElement(divLoading);
    showElement(divMain);
}

function tableContentCapture() { 
    
    let hide1 = document.getElementsByClassName("btn-bold");
    let hide2 = document.getElementsByClassName('dwld');
    let hides = [].concat(Array.from(hide1))
                  .concat(Array.from(hide2));
    
    for (let hide of hides) {
        hideElement(hide);
    }

    let store = sourceStoreMap[selectStoreId];
    let fileName = inputDate.value.replaceAll("-", "") + "_" + store.storeName;

    html2canvas(document.querySelector("#divContent")).then(function(canvas) {
        a = document.createElement('a');
        a.href = canvas.toDataURL("image/jpeg", 0.92).replace("image/jpeg", "image/octet-stream");
        a.download = fileName + '.jpg';
        a.click();

        for (let hide of hides) {
            showElement(hide);
        }
        // showElement(hide2);
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

function clickColl(tri) {
    tri.style.transform = 'rotate(90deg)';
    // if (tri.style.transform) {
    //     tri.style.removeProperty('transform');
    // }
    // else {
    //     tri.style.transform = 'rotate(90deg)';
    // }
    
    let collapse = document.getElementsByClassName('collapse-body');
    for (let i = 0; i < collapse.length; i++) {
        collId = collapse[i].id;
        openColl(collId);
    }
    tri.style.removeProperty('transform');
}

refresh();