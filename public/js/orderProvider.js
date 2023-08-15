//0:現金, 1:郵局, 2: linePay, 3: 街口, 4: 中信, 5: 國泰, 6: 永豐, 7: 台灣pay
const PaymentType = Object.freeze({ "cash": 0, "post": 1, "linePay": 2, "jkos": 3, "ctbc": 4, "cathay": 5, "sinopac": 6, "taiwanPay": 7 });

const DiscountType = Object.freeze({ "normal": 0, "activity": 1, "coupon": 2 });

const StoreExtraPricingType = Object.freeze({ "storeMealLoss": 0, "howqponMealLoss": 1, "storeFreight": 2});

const CaculateOnlinePayType = Object.freeze({ "none": 0, "normal": 1, "dessert": 2 });

const OrderSourceType = Object.freeze({ "normal": 0, "turn": 1 });

function string2PaymentType(type) {
    if (type == "現金") {
        return PaymentType.cash;
    } else if (type == "郵局") {
        return PaymentType.post;
    } else if (type == "LINEpay") {
        return PaymentType.linePay;
    } else if (type == "街口") {
        return PaymentType.jkos;
    } else if (type == "中信") {
        return PaymentType.ctbc;
    } else if (type == "國泰") {
        return PaymentType.cathay;
    } else if (type == "永豐") {
        return PaymentType.sinopac;
    } else if (type == "台灣pay") {
        return PaymentType.taiwanPay;
    }

    return null;
}

function tranStoreExtraPricingType2Text(type) {
    if (type == StoreExtraPricingType.storeMealLoss) {
        return "店家餐損";
    } else if (type == StoreExtraPricingType.howqponMealLoss) {
        return "好客萌餐損";
    } else if (type == StoreExtraPricingType.storeFreight) {
        return "店家運費";
    }

    return null;
}

function combineMealName(meal, showMemo) {
    return combineMeal(meal, showMemo).name;
}

function combineMealAmount(meal) {
    return combineMeal(meal, true).amount;
}

function combineMeal(meal, showMemo) {
    var mealName = meal.mealName;
    var mealAmount = Number(meal.amount);

    var showNameHTML = `<div style="display:flex; flex-wrap: wrap;">`;
    showNameHTML += `<div style="font-weight: bold">${mealName}</div>`;

    if (meal.extraMeals != null) {
        meal.extraMeals.forEach(extraMeal => {
            showNameHTML += `<div">(${extraMeal.mealName})</div>`;
            mealAmount += (extraMeal.qty ?? 1) * Number(extraMeal.amount);
        });
    }

    if (meal.groupOptions != null) {
        var groupOptionNameHTML = "";
        meal.groupOptions.forEach((groupOption, groupOptionIndex) => {
            var groupOptionAmount = 0;
            var groupOptionNames = [];
            var options = groupOption.options ?? [];
            if(options.length > 0){
                options.forEach(option => {
                    groupOptionNames.push(option.name);
                    groupOptionAmount += option.amount;
                });
                groupOptionNameHTML += `<font style="font-weight: normal;  border: 2px solid #D3D3D3; margin-left:2px;">${groupOptionNames.join(",")}</font>`;
                mealAmount += groupOptionAmount;
            }
        });

        if(groupOptionNameHTML != ""){
            showNameHTML += `<div">&nbsp;/${groupOptionNameHTML}</div>`;
        }
    }

    if(showMemo && isNotEmpty(meal.memo)){
        showNameHTML += `<div">&nbsp;/(${meal.memo})</div>`;
    }

    showNameHTML += "</div>";
    return {
        "name": showNameHTML,
        "amount": mealAmount,
    };
}

function caculateOnlinePayDiscount(onlinePayType, storeOrders) {
    var discount = 0;

    if (onlinePayType == CaculateOnlinePayType.none) {
        return 0;
    }

    if (onlinePayType == CaculateOnlinePayType.normal) {
        var count = 0;
        storeOrders.forEach(storeOrder => {
            storeOrder.meals.forEach(meal => {
                var amount = combineMealAmount(meal);
                if (amount >= 90) {
                    count += meal.qty;
                }
            });
        });

        var discountLevel = {
            "60": 200,
            "30": 100,
            "25": 70,
            "20": 50,
            "15": 30,
            "10": 15,
            "6": 5
        };
        var levels = Object.keys(discountLevel);
        levels.sort((a, b) => {
            return -parseInt(a) + parseInt(b);
        });
        for (var level of levels) {
            if (count >= parseInt(level)) {
                discount = -discountLevel[level];
                break;
            }
        }
    } else if (onlinePayType == CaculateOnlinePayType.dessert) {
        // var count = 0;
        // storeOrders.forEach(storeOrder => {
        //     storeOrder.meals.forEach(meal => {
        //         var amount = combineMealAmount(meal);
        //         if (amount >= 60) {
        //             count += meal.qty;
        //         }
        //     });
        // });

        // var discountLevel = {
        //     "60": 200,
        //     "30": 100,
        //     "25": 70,
        //     "20": 50,
        //     "15": 30,
        //     "10": 15,
        //     "6": 5
        // };
        // var levels = Object.keys(discountLevel);
        // levels.sort((a, b) => {
        //     return -parseInt(a) + parseInt(b);
        // });
        // for (var level of levels) {
        //     if (count >= parseInt(level)) {
        //         discount = -discountLevel[level];
        //         break;
        //     }
        // }
    }


    return discount;
}