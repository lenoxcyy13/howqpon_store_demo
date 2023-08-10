<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Line Login Demo</title>

    <!-- Vendor CSS Files -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/9.1.1/swiper-bundle.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/glightbox/3.2.0/css/glightbox.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.css" rel="stylesheet">

    <!-- SVGs -->
    <?php include("images/home/svg.php"); $svg = new SVGs;?>

    <style>
        .login_content {
            display: flex;
            flex-direction: column;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="login_content">
            <h1>登入</h1>
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