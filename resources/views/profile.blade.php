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

    <title>店家管理</title>

    <style>

        html {
            overflow: hidden;
        }

        .media {
            max-width: 100%;
            height: auto;
        }

        /* desktop */
        @media (min-width: 992px) {
            .media {
                max-width: 800px;
            }
            #divOwnStores {
                width: 30%;
            }
        }

        /* iPad and iPad Mini */
        @media (min-width: 768px) and (max-width: 991px) {
            .media {
                max-width: 600px;
            }
            #divOwnStores {
                width: 50%;
            }
        }

        /* iPhone and similar devices */
        @media (max-width: 767px) {
            .media {
                max-width: 100%;
            }
            #divOwnStores {
                width: 80%;
            }
        }

        .nav-btn {
            
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
            height: 100%;
        }

        .nav-links {
            position: absolute;
            display: block;
            height: 0px;
            width: 100%;
            background-color: var(--green-50);
            transition: all 0.2s ease-in;
            overflow-y: hidden;
            top: 66px;
            left: 0px;
        }

        .nav-links > a {
            display: inline-block;
            padding: 13px 10px 13px 10px;
            text-decoration: none;
            color: var(--grey-900);
            width: 100%;
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

        #divOwnStores {
            display: flex;
            flex-direction: column;
            align-items: start;
            margin: auto;
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

    <div id="divMain">
        <div class="mt-4 text-center">
            <div>
                <img id="imgPicture" style="border-radius: 50%; width:150px;"></img>
            </div>
            <div style="font-size:24px; margin:8px">
                <font id="fontName"></font>
            </div>
            <div id=divOwnStores></div>
        </div>
    </div>
    </div>

</body>
</html>

<script src="js/tools.js"></script>
<script src="js/html2canvas.min.js"></script>
<script src="js/orderProvider.js"></script>

<script>
    var data = '<?php echo $data; ?>';
    sourceData = Base64.decode(data);
    sourceData = Base64.decode(sourceData);
    sourceData = JSON.parse(sourceData);

    imgPicture.src = sourceData.pictureUrl;
    fontName.innerHTML = sourceData.name;

    async function getStoreInfo() {
        result = await callGetApi(HOST + "/api/getStores");
        let stores = JSON.parse(result.response);
        sourceStoreMap = stores.reduce(function(map, obj) {
            map[obj.storeId] = obj;
            return map;
        }, {});


        const div = document.getElementById('divOwnStores');
        for (let i = 0; i < sourceData.roles.length; i++){
            const store = sourceStoreMap[sourceData.roles[i].storeId];
            const div_store = document.createElement("div");
            div_store.classList.add('divStore');
            div_store.style.display = "flex";
            div_store.style.justifyContent= "space-between";
            div_store.style.width = "100%";
            div_store.style.paddingBottom = "5px";

            const btn_store = document.createElement("button");
            btn_store.classList = 'btn';
            btn_store.onclick = () => window.location = `store?storeId=${store.storeId}.html`;
            btn_store.innerHTML = store.storeName;
            btn_store.innerHTML += '的';

            if(sourceData.roles[i].roleId == '0'){
                btn_store.innerHTML += '好客萌小編';
            }
            if(sourceData.roles[i].roleId == '1'){
                div_store.style.flexDirection = "row-reverse";
                btn_store.innerHTML += '店長';
                const btn_link = document.createElement("button");
                btn_link.classList = 'btn btn-secondary';
                btn_link.innerHTML = '指派店員';
                btn_link.onclick = () => generateToken(store.storeNo)
                div_store.appendChild(btn_link);
            }
            if(sourceData.roles[i].roleId == '2'){
                btn_store.innerHTML += '店員';
            }
            
            div_store.appendChild(btn_store);
            div.appendChild(div_store);
        }
    }

    async function generateToken(storeNo) {
        const result = await callGetApi("http://127.0.0.1:8001/api/storeLoginToken?storeNo=" + storeNo + "&role=clerk");
        const returnURL = `http://127.0.0.1:8003/login?token=${result.response}`;
        setTimeout(async () => {
            await navigator.clipboard.writeText(returnURL);
            alert("已複製連結");
        }, 100);
        // return returnURL
    }

    getStoreInfo();


</script>