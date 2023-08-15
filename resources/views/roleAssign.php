<!DOCTYPE html>
<html>

<head>

    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="Access-Control-Allow-Origin" content="*" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="css/mystyle.css?v=1">

    <title>身份</title>
    
</head>

<body>

    <div id="divHeader">
        <!-- <a href="index.html"><img width="240" height="60" src="images/logo.png" alt="howqpon"></a> -->
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
var userId = '<?php echo $userId; ?>';
</script>