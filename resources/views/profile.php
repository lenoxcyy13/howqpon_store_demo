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
    <!-- <link href="css/bootstrap.min.css" rel="stylesheet"> -->

    <title>身份</title>

    <style>
        #divHeader {
            display: flex;
            align-items: center;
            box-sizing: border-box;
            justify-content: space-between;
        }

    </style>
    
</head>

<body>

    <div id="divHeader">
        <button class="btn btn-block" type="submit" onclick="window.location.href='store.html'">訂單</button>
        <a href="store.html"><img width="54" height="54" src="images/howqpon.ico" alt="howqpon"></a>
        <button class="btn btn-block" type="submit" onclick="window.location.href='logout'">登出</button>
    </div>

    <div id='divLoading' style="display: none;">正在載入頁面資料...</div>
    <div id="divMain" style="display: none;">
    </div>
    </div>

</body>
</html>

<script src="js/tools.js"></script>
<script src="js/html2canvas.min.js"></script>
<script src="js/orderProvider.js"></script>

<script>
var userId = `<?php echo $userId; ?>`;
</script>