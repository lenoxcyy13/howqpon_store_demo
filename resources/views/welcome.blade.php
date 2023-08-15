<!DOCTYPE html>
<html>

<head>

    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="Access-Control-Allow-Origin" content="*" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Template Main CSS Files -->
    <link href="{{ asset('css/home/variables.css') }}" rel="stylesheet">
    <link href="{{ asset('css/home/main.css') }}" rel="stylesheet">
    <link href="{{ asset('css/home/custom.css') }}" rel="stylesheet">
    <link href="css/mystyle.css" rel="stylesheet">
    <link href="css/bootstrap.css" rel="stylesheet">
    <!-- <link href="css/bootstrap.min.css" rel="stylesheet"> -->

    <title>本日訂單</title>

    <style type>

        #divHeader {
            display: flex;
            align-items: center;
            box-sizing: border-box;
            justify-content: space-between;
        }

        #divMain {
            width: auto;
            height: auto;
            font-size: 14px;
        }

        .td_update {
            text-align: center;
            border: 0;
        }

        /* .btn {
            border-style: groove;
            padding: 5px;
        } */

    </style>
    
</head>

<body>

    <div id="divHeader">
        <a href="index.html"><img width="54" height="54" src="images/howqpon.ico" alt="howqpon"></a>
        <button class="btn btn-block" type="submit" onclick="window.location.href='logout'">登出</button>
    </div>

    <div id='divLoading' style="display: none;">正在載入頁面資料...</div>
    <div id="divMain" style="display: none;">
        訂單確認 日期：<input id="inputDate" type="date" onchange="refresh()"></input><br><br>
        <div>
            <div id="divStores" class="floatLeft"></div>
            <div class="floatLeft">
                <div id="divSort">
                    顯示服務費<input id="inputCbkShowFeeAmount" type="checkbox" oninput="showStore(storeId)" checked></input>
                </div>
                <div id="divContent"></div>
            </div>
        </div>
    </div>

</body>
</html>

<script src="{{ asset('js/tools.js') }}"></script>
<script src="{{ asset('js/html2canvas.min.js') }}"></script>
<script src="{{ asset('js/orderProvider.js') }}"></script>

<script>
var data = '<?php echo $data; ?>';
</script>

<script src="{{ asset('js/ordersToday.js') }}"></script>