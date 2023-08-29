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

        table {
            width: 100%;
        }

        .td_no {
            text-align: left;
            padding-left: 5px;
            width: 75px;
        }

        .td_meal {
            text-align: left;
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
            border-bottom-width: 4px;
        }

        .tabNav:hover {
            font-weight: 500;
            outline: none;
        }

        .tabNav.Confirm:hover, .tabNav.Confirm.Selected {     
            border-bottom-color: var(--green-500);
            color: var(--green-500) !important;
        }

        .tabNav.notConfirm:hover, .tabNav.notConfirm.Selected {     
            border-bottom-color: var(--red-500);    
            color: var(--red-500) !important;
        }

        .collapse-head, .collapse-head:hover {
            display: flex;
            justify-content: space-between;
            font-family: monospace;
            font-weight: bold;
            font-size: 16px !important;
            width: 100%
        }

        .focus, .collapsible:hover {
            background-color: #ccc;
        }

        .collapse-body {
            padding: 0px;
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
        <a href="profile.html"><img width="54" height="54" src="images/howqpon.ico" alt="howqpon"></a>
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
                <div id="divSort" style="display: ">
                    顯示服務費<input id="inputCbkShowFeeAmount" type="checkbox" oninput="showStore(storeId)" checked>
                </div>
                <div id="divTotal" class="navBar">
                    <button id="btn_not" class="tabNav notConfirm Selected" onclick="openTab('btn_not', 'divNotConfirm', 'confirm')">待確認</button>
                    <button id="btn_confirm" class="tabNav Confirm" onclick="openTab('btn_confirm', 'divConfirm', 'confirm')">已確認</button>
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

    let btnFocus = document.getElementById('btn_not');

    function openTab(btnId, tabId, className) {
        event.preventDefault();
        var i;
        var x = document.getElementsByClassName(className);
        for (i = 0; i < x.length; i++) {
            x[i].style.display = "none";
        }
        document.getElementById(tabId).style.display = "block";

        let button = document.getElementById(btnId);
        if (btnFocus != button) {
            if (btnFocus) {
                btnFocus.blur();
                btnFocus.classList.remove('Selected');
            }
            button.focus();
            button.classList.add('Selected');
            btnFocus = button;
        }

        setInterval(myCallback, 100);
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

