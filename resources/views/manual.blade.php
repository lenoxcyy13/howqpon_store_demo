<!DOCTYPE html>
<html>

<head>

    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="Access-Control-Allow-Origin" content="*" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Template Main CSS Files -->
    <link href="css/home/variables.css" rel="stylesheet">
    <link href="css/home/main.css" rel="stylesheet">
    <link href="css/home/custom.css" rel="stylesheet">
    <link href="css/mystyle.css" rel="stylesheet">
    <link href="css/bootstrap.css" rel="stylesheet">

    <title>使用說明</title>

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
            <a href="manual.html">使用說明</a>
        </div>

        <img width="54" height="54" src="images/howqpon.ico" alt="howqpon"></a>
        <button class="btn btn-block" type="submit" onclick="window.location.href='logout'">登出</button>
    </div>

    <div id='divLoading' style="display: none;">正在載入頁面資料...</div>
    <div class='container'>
        <div id="divMain" style="display: none;">

        </div>
    </div>

</body>
</html>

<script src="js/tools.js"></script>
