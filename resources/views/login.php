<!DOCTYPE html>
<html>

<head>

    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="Access-Control-Allow-Origin" content="*" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Vendor CSS Files -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/9.1.1/swiper-bundle.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/glightbox/3.2.0/css/glightbox.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.css" rel="stylesheet">
    
    <!-- Template Main CSS Files -->
    <link href="css/home/variables.css" rel="stylesheet">
    <link href="css/home/main.css" rel="stylesheet">
    <link href="css/home/custom.css" rel="stylesheet">
    <link href="css/mystyle.css" rel="stylesheet">
    <!-- <link href="css/bootstrap.css" rel="stylesheet"> -->
    <!-- <link href="css/bootstrap.min.css" rel="stylesheet"> -->

    <!-- SVGs -->
    <?php include("images/home/svg.php"); $svg = new SVGs;?>

    <title>Line Login Demo</title>

    <style>

        #divHeader {
            display: flex;
            align-items: center;
            box-sizing: border-box;
            justify-content: center;
        }

        .login_content {
            display: flex;
            flex-direction: column;
            text-align: center;
        }

        /* .btn {
            border-style: groove;
            padding: 5px;
        } */

    </style>
</head>
<body>

    <div id="divHeader">
        <a href="profile.html"><img width="54" height="54" src="images/howqpon.ico" alt="howqpon"></a>
        <!-- <button class="btn btn-block" type="submit" onclick="window.location.href='logout'">登出</button> -->
    </div>

    <div class="content">
        <div class="login_content">
            <div style="height: 30px"></div>
            <h1>店家登入</h1>
            <a href="lineLogin?mode=1">
                <button class="btn px-5 py-3 px-lg-5 btn-lg" style="background-color:#06C755 !important;">
                <h3 class="m-0" style="color: white"> <?=$svg->line?> Log in with LINE</h3></button></a>
            <div  class="mt-3 mb-5">
                <h6>登入即代表您同意&nbsp;<a href="privacy.html">隱私權條款</a></h6>
            </div>
        </div>
    </div>

</body>
</html>