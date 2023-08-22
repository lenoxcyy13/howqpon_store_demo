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

    <style>

        html {
            overflow-y: scroll;
        }

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

        #divTitle {
            text-align: center;
        }

        table td {
            border: 1px solid #D3D3D3;
        }

        .td_update {
            text-align: center;
            border: 0;
        }

        #divTotal {
            display: flex;
            justify-content: space-between;
            height: 50px;
        }

        .tabNav {
            width: 50%;
            border-style: solid;
            border-color: #efefef;
        }

        .tabNav:hover {     
            border-bottom-color: var(--green-500);;    
        }

        .collapse-head {
            font-weight: bold;
        }

        .active, .collapsible:hover {
            background-color: #ccc;
        }

        .collapse-body {
            padding: 0 18px;
            overflow: hidden;
            display: block;
            text-align: center;
            max-height: 0px;
            transition: max-height 0.2s ease-out;
        }
    </style>
    
</head>

<body>

    <div id="divHeader">
        <button class="btn btn-block" type="submit" onclick="window.location.href='profile.html'">用戶</button>
        <a href="store.html"><img width="54" height="54" src="images/howqpon.ico" alt="howqpon"></a>
        <button class="btn btn-block" type="submit" onclick="window.location.href='logout'">登出</button>
    </div>

    <div id='divLoading' style="display: none;">正在載入頁面資料...</div>
    <div id="divMain" style="display: none;">
        <div id="divTitle">
            <h4 id="pStoreName"></h4>
            日期：<input id="inputDate" type="date" onchange="refresh()">
        </div>
        <div>
            <div id="divStores" class="floatLeft" style="display: none"></div>
            <div class="floatLeft">
                <div id="divSort" style="display: none">
                    顯示服務費<input id="inputCbkShowFeeAmount" type="checkbox" oninput="showStore(storeId)" checked>
                </div>
                <div id="divTotal" class="navBar">
                    <button id="btn_not" class="tabNav" onclick="openTab('divNotConfirm', 'confirm')">待確認</button>
                    <button id="btn_confirm" class="tabNav" onclick="openTab('divConfirm', 'confirm')">已確認</button>
                </div>
                <div id="divContent">
                    <div id="divNotConfirm" class="confirm"></div>
                    <div id="divConfirm" class="confirm" style="display:none"></div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>

<script src="{{ asset('js/tools.js') }}"></script>
<script src="{{ asset('js/html2canvas.min.js') }}"></script>
<script src="{{ asset('js/orderProvider.js') }}"></script>

<script>var data = '<?php echo $data; ?>';</script>
<script src="{{ asset('js/ordersToday.js') }}"></script>

<script>
    
    function openTab(idName, className) {
        var i;
        var x = document.getElementsByClassName(className);
        for (i = 0; i < x.length; i++) {
            x[i].style.display = "none";
        }
        document.getElementById(idName).style.display = "block";
    }

    function openColl(idName) {
        var content = document.getElementById(idName);
        if (content.style.maxHeight) {
            content.style.maxHeight = null;
        } 
        else {
            content.style.maxHeight = content.scrollHeight + "px";
        }
    }

    function myCallback() {
        const coll = document.getElementsByClassName("btn btn-secondary");      
        if (coll.length) {
            clearInterval(myInterval);
            for (let i = 0; i < coll.length; i++) {
                const tabl = document.getElementsByClassName("collapse-body isOpen");
                for (let i = 0; i < tabl.length; i++) {
                    tabl[i].style.maxHeight = tabl[i].scrollHeight + "px";
                }
            }
        }
    }

    const myInterval = setInterval(myCallback, 100);


</script>

