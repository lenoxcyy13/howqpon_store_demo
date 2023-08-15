sourceData = Base64.decode(data);
sourceData = Base64.decode(sourceData);
sourceData = JSON.parse(sourceData);
console.log(sourceData);

var currentTime = new Date();
if (currentTime.getHours() >= 18) {
    currentTime.addDays(1);
}
inputDate.value = dateTime2YMD(currentTime);

const storeId = sourceData.storeId;

async function refresh() {
    divLoading.innerHTML = "正在載入頁面資料...";

    showElement(divLoading);
    hideElement(divMain);

    divStores.innerHTML = "";
    divContent.innerHTML = "";

    var result = await callGetApi("http://127.0.0.1:8001/api/getOrders?filterDate=" + inputDate.value.replaceAll("-", ""));
    orders = JSON.parse(result.response);
    orders = orders.filter(order => order.totalAmount != 0);

    if (orders.length > 0) {
        result = await callGetApi("http://127.0.0.1:8001/api/getStores");
        var stores = JSON.parse(result.response);
        sourceStoreMap = stores.reduce(function(map, obj) {
            map[obj.storeId] = obj;
            return map;
        }, {});

        showStore(storeId);
    } else {
        divStores.innerHTML = inputDate.value + " 尚無訂單";
    }

    hideElement(divLoading);
    showElement(divMain);
}

function showStore(storeId) {
    selectStoreId = storeId;

    var sourceStore = sourceStoreMap[storeId];
    var storeName = sourceStore.storeName;

    var firstRowColor = "#a2dce1";
    if (sourceStore.uiSettings?.colorForParseOrder2Store != null) {
        firstRowColor = sourceStore.uiSettings.colorForParseOrder2Store;
    }

    var tmp = "";
    tmp += createRow("單號", "店家", "品項", "數量", "價格", "小計", "出餐", firstRowColor);

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
    results.forEach(result => {
        if (!isFirstOrder) {
            tmp +=
                `<td colspan="8" style="height: 10px"></td>`;
        }

        var order = result.order;
        orderDate = order.orderDate.split("-")[1] + "-" + order.orderDate.split("-")[2];
        var storeOrder = result.storeOrder;
        var meals = storeOrder.meals;

        tmp += createUpdateRow(order.orderNo, storeName, "--", "", "", "", storeOrder.expectTime ?? "","","","","");
            // storeOrder.isCheckOrder ?
            // `<button onclick="updateCheckStoreOrder('${order.orderId}', '${storeId}', false)">取消叫餐</button>` :
            // `<button onclick="updateCheckStoreOrder('${order.orderId}', '${storeId}', true)" style="background-color:#f5c5c6;">去叫餐</button>`,
            // "", "", storeOrder.isMarkExpectTimeUpdate ? "blue" : "");

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
    
            if (mealIndex == 0) {
                tmp += createUpdateRow("", storeName, mealName, meal.qty,
                    parseNumber2ShowThousand(mealAmount), parseNumber2ShowThousand(mealAmount * meal
                        .qty), "",
                    storeOrder.updateCheckOrderText != null ?
                    `${storeOrder.updateCheckOrderText}<button onclick="updateCheckStoreOrder('${order.orderId}', '${storeId}', true)" style="background-color:#f5c5c6;">再次確認</button>` :
                    "", "", mealColor, "");
            } else {
                tmp += createRow("", storeName, mealName, meal.qty, parseNumber2ShowThousand(
                        mealAmount), parseNumber2ShowThousand(mealAmount * meal.qty), "", "",
                    mealColor);
            }
        });

        tmp += createUpdateRow("", "總計", "--", totalQty, "--", parseNumber2ShowThousand(totalAmount), "",
            (storeOrder.isStoreConfirm ?? false) ?
            `<button class="btn btn-secondary" style="writing-mode: vertical-lr;" onclick="updateStoreOrderConfirm('${order.orderId}', '${storeId}', false)">取消確認</button>` :
            `<button class="btn btn-failed btn-bold" style="writing-mode: vertical-lr;" onclick="updateStoreOrderConfirm('${order.orderId}', '${storeId}', true)">確認請點我</button>`,
            "", "", "");

        var feeAmount = storeOrder.feeAmount;
        if (inputCbkShowFeeAmount.checked) {
            tmp += createRow("", "服務費", "--", "--", "", "-" + parseNumber2ShowThousand(feeAmount), "", "", "");
        }

        if(storeOrder.storeMealLossAmount != null && storeOrder.storeMealLossAmount > 0){
            tmp += createRow("", "餐損(店家)", "--", "--", "", "-" + parseNumber2ShowThousand(storeOrder.storeMealLossAmount), "", "#FF8153", "");
        }

        if(storeOrder.storeFreightAmount != null && storeOrder.storeFreightAmount > 0){
            tmp += createRow("", "運費(店家)", "--", "--", "", "-" + parseNumber2ShowThousand(storeOrder.storeFreightAmount), "", "", "");
        }

        var storePayableAmount = (totalAmount - feeAmount - (storeOrder.storeMealLossAmount ?? 0) - (storeOrder.storeFreightAmount ?? 0));
        tmp += createRow("", "應付帳款", "--", "--", "", parseNumber2ShowThousand(storePayableAmount), "", "", "");
        totdayStoreTotalAmount += storePayableAmount;

        tmp += createRow("", "備註", storeOrder.memo ?? "", "", "", "", "", "", "");
        tmp += createRow("", "餐具", order.isNeedTableware ? "是" : "否", "", "", "", "", "", "");
        tmp += createRow("", "收據", order.isNeedReceipt ?
            `是${order.taxIdNumber != null ? `(統編${order.taxIdNumber})` : ""}` : "否", "", "", "", "", "", "");
        isFirstOrder = false;

        tmp += `<tr>
            <td colspan="7" style="text-align:center; color:red; font-weight: 600">餐點請務必先裝入塑膠袋，再裝入保溫袋</td>
            </tr>`;
    });

    var contentHTML =
        `<a href="javascript:void(0)" onclick="tableContentCapture()">下載成圖檔</a>`;

    var info = sourceStore.uiSettings?.pasrseOrder2StoreInfo;
    if(info != null){
        contentHTML += `<div style="color:red;font-size:20px; border: 1px solid red;"><font style="font-weight:bold;">叫餐提醒說明：</font><br>${info}</div>`;
    }
    contentHTML += "<br>";
    contentHTML += `<table id='tableContent'>${createRow(orderDate, "所有訂單總價", parseNumber2ShowThousand(totdayStoreTotalAmount), "", "", "", "", firstRowColor)}${tmp}</table>`;

    divContent.innerHTML = contentHTML;
}

function createUpdateRow(r1, r2, r3, r4, r5, r6, r7, r8, bgcolor, mealNameColor, timeColor) {
    return `<tr bgcolor="${bgcolor}">
            <td class="td_no" onclick="onClickTd(this)">${r1}</td>
            <td class="td_store" onclick="onClickTd(this)">${r2}</td>
            <td class="td_meal" onclick="onClickTd(this)" style="color:${mealNameColor}">${r3}</td>
            <td class="td_price" onclick="onClickTd(this)">${r4}</td>
            <td class="td_qty" onclick="onClickTd(this)">${r5}</td>
            <td class="td_amount" onclick="onClickTd(this)">${r6}</td>
            <td class="td_time" onclick="onClickTd(this)" style="color:${timeColor}">${r7}</td>
            <td class="td_update">${r8}</td>
            </tr>`;
}

function createRow(r1, r2, r3, r4, r5, r6, r7, bgcolor, mealNameColor) {
    if (r2 == "收據" && (r3.indexOf("是") == 0)) {
        return `<tr bgcolor="${bgcolor}">
            <td class="td_no">${r1}</td>
            <td class="td_store">${r2}</td>
            <td class="td_meal" style="color:red">${r3}</td>
            <td class="td_price">${r4}</td>
            <td class="td_qty">${r5}</td>
            <td class="td_amount">${r6}</td>
            <td class="td_time">${r7}</td>
            </tr>`;
    }

    if (r2 == "所有訂單總價" || r1 == "單號") {
        return `<tr class="tr_top" onclick="onClickTopTr(this)" style="background-color: ${bgcolor};">
            <td class="td_no">${r1}</td>
            <td class="td_store">${r2}</td>
            <td class="td_meal">${r3}</td>
            <td class="td_price">${r4}</td>
            <td class="td_qty">${r5}</td>
            <td class="td_amount">${r6}</td>
            <td class="td_time">${r7}</td>
            </tr>`;
    }

    return `<tr style="background-color: ${bgcolor};">
        <td class="td_no" onclick="onClickTd(this)">${r1}</td>
        <td class="td_store" onclick="onClickTd(this)">${r2}</td>
        <td class="td_meal" onclick="onClickTd(this)" style="color:${mealNameColor}">${r3}</td>
        <td class="td_price" onclick="onClickTd(this)">${r4}</td>
        <td class="td_qty" onclick="onClickTd(this)">${r5}</td>
        <td class="td_amount" onclick="onClickTd(this)">${r6}</td>
        <td class="td_time" onclick="onClickTd(this)">${r7}</td>
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
    }

    hideElement(divLoading);
    showElement(divMain);
}

function tableContentCapture() {
    var tdUpdates = document.getElementsByClassName("td_update");

    for (let element of tdUpdates) {
        hideElement(element);
    }

    var store = sourceStoreMap[selectStoreId];
    var fileName = inputDate.value.replaceAll("-", "") + "_" + store.storeName;

    html2canvas(document.querySelector("#tableContent")).then(function(canvas) {
        a = document.createElement('a');
        a.href = canvas.toDataURL("image/jpeg", 0.92).replace("image/jpeg", "image/octet-stream");
        a.download = fileName + '.jpg';
        a.click();

        for (let element of tdUpdates) {
            showElement(element);
        }
    });
}

refresh();