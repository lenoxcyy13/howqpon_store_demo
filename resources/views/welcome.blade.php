<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <title>Store</title>

        <!-- Fonts -->

        <!-- Styles -->
        <style>
        </style>
    </head>
    <body>
        <h1>Login Success!</h1>
    </body>
</html>

<script>

var data = '<?php echo $data; ?>';


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
                "<tr><td class='td_hr'></td><td class='td_hr'></td><td class='td_hr'></td><td class='td_hr'></td><td class='td_hr'></td><td class='td_hr'></td><td class='td_right_border'></td></tr>";
        }

        var order = result.order;
        orderDate = order.orderDate.split("-")[1] + "-" + order.orderDate.split("-")[2];
        var storeOrder = result.storeOrder;
        var meals = storeOrder.meals;

        tmp += createUpdateRow(order.orderNo, storeName, "--", "", "", "", storeOrder.expectTime ?? "",
            storeOrder.isCheckOrder ?
            `<button onclick="updateCheckStoreOrder('${order.orderId}', '${storeId}', false)">取消叫餐</button>` :
            `<button onclick="updateCheckStoreOrder('${order.orderId}', '${storeId}', true)" style="background-color:#f5c5c6;">去叫餐</button>`,
            "", "", storeOrder.isMarkExpectTimeUpdate ? "blue" : "");

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
            `<button onclick="updateStoreOrderConfirm('${order.orderId}', '${storeId}', false)">取消店家確認</button>` :
            `<button onclick="updateStoreOrderConfirm('${order.orderId}', '${storeId}', true)" style="background-color:#f5c5c6;">店家確認後點我</button>`,
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
            <td colspan="7" style="text-align:center; color:red; font-size:20px;">餐點請務必先裝入塑膠袋，再裝入保溫袋</td>
            </tr>`;
    });

    var contentHTML =
        `<a href="javascript:void(0)" onclick="tableContentCapture()">下載成圖檔</a>
        &nbsp;<a href="javascript:void(0)" onclick="updateAllCheckStoreOrder('${storeId}')">一鍵叫餐</a>
        &nbsp;<a href="javascript:void(0)" onclick="updateAllStoreOrderConfirm('${storeId}')">一鍵店家確認</a><br>`;

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
</script>
