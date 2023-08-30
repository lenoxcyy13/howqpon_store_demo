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

        body::-webkit-scrollbar{
            display: none;
        }

        .media {
            max-width: 100%;
            /* height: auto; */
        }

        /* desktop */
        @media (min-width: 992px) {
            .media {
                max-width: 800px;
            }
            .container {
                width: 50%;
            }
        }

        /* iPad and iPad Mini */
        @media (min-width: 768px) and (max-width: 1180px) {
            .media {
                max-width: 600px;
            }
            .container {
                width: 70%;
            }
        }

        /* iPhone and similar devices */
        @media (max-width: 767px) {
            .media {
                max-width: 100%;
            }
            .container {
                width: auto;
            }
            table {
                font-size: 14px;
            }
        }

        #divHeader {
            display: flex;
            align-items: center;
            box-sizing: border-box;
            justify-content: space-between;
        }

        .nav-btn > label {
            display: inline-block;
            width: 50px;
            height: 50px;
            padding: 13px;
        }

        .nav-btn > label > span {
            display: block;
            width: 25px;
            height: 10px;
            border-top: 2px solid var(--green-500);
        }

        .nav-btn > label:hover,.nav  #nav-check:checked ~ .nav-btn > label {
            background-color: var(--green-50);
        }

        #nav-check:checked ~ .nav-links {
            z-index: 9999;
            height: 100%;
        }

        #nav-check:not(:checked) ~ .nav-links {
            height: 0px;
        }

        .nav-links {
            z-index: 9999;
            position: absolute;
            display: block;
            width: 100%;
            background-color: var(--green-50);
            height: 0px;
            transition: all 0.2s ease-in;
            overflow-y: hidden;
            top: 66px;
            left: 0px;
        }

        .nav-links > a {
            display: inline-block;
            padding: 13px 10px 13px 10px;
            text-decoration: none;
            color: var(--grey-800);
            width: 100%;
        }

        .container {
            display: flex;
            justify-content: center;
            padding: 0px;
        }

        .floatLeft {
            /* overflow-y: scroll; */
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
        <input type="checkbox" id="nav-check" style="display: none">
        <div class="nav-btn" style="display: flex;">
            <label for="nav-check">
                <span></span>
                <span></span>
                <span></span>
            </label>
        </div>
        <div class="nav-links">
            <a href="profile.html">店家管理</a>
            <a href="profile.html">使用說明</a>
        </div>

        <img width="54" height="54" src="images/howqpon.ico" alt="howqpon"></a>
        <button class="btn btn-block" type="submit" onclick="window.location.href='logout'">登出</button>
    </div>

    <div id='divLoading' style="display: none;">正在載入頁面資料...</div>
    <div class='container'>
        <div id="divMain" style="display: none;">
            <div id="divTitle">
                <h4 id="pStoreName"></h4>
                日期：<input id="inputDate" type="date" onchange="refresh()">
            </div>
            <div>
                <div id="divStores" class="floatLeft" style="display: none"></div>
                <div class="floatLeft">
                    <div id="divSort" style="display: ">
                        顯示服務費<input id="inputCbkShowFeeAmount" type="checkbox" oninput="showFee(storeId)" checked>
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
    let first = true;

    function openTab(btnId, tabId, className) {
        let x = document.getElementsByClassName(className);
        for (let i = 0; i < x.length; i++) {
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

        if(btnId == "btn_not") {
            if (first) {
                myInterval = setInterval(myCallback, 50);
                first = false;
            }
        }
        else { first = true; }
    }

    function openColl(idName) {
        const content = document.getElementById(idName);
        if (content.style.maxHeight != "0px") {
            content.style.maxHeight = "0px";
        } 
        else {
            content.style.maxHeight = content.scrollHeight + "px";
        }
    }

    function myCallback() {
        const tabl = document.getElementsByClassName("collapse-body isOpen");
        if (tabl.length) {
            clearInterval(myInterval);
            for (let i = 0; i < tabl.length; i++) {
                tabl[i].style.maxHeight = tabl[i].scrollHeight + "px";
            }
        }
    }

    function showFee(storeId) {
        showStore(storeId);
        myInterval = setInterval(myCallback, 50);
    }

    let myInterval = setInterval(myCallback, 50);

</script>

