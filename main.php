<?php
session_start();
$login;
$isLogin;
if(isset($_SESSION['account']))
{
    try{
      $pdo = new PDO('mysql:host=localhost;dbname=Userinfo','root','root');
    }
    catch(Exception $e){
        $login = "<a href='./index.html'>登录</a>";
        $isLogin = 0;
    }
    $account = $_SESSION['account'];
    $sql = "select * from basicinfo where account='".$account."';";
    $result = $pdo->query($sql);
    $rows = $result->fetchAll();

    $isLogin = 0;
    for ($i = 0; $i < count($rows); $i++) {
        $login = $rows[$i]["name"];
        $isLogin = 1;

    }
}
else
{
    $login = "<a class='username' href='./index.html'>Signin</a>";
    $isLogin = 0;
}

?>
<!DOCTYPE html>
    <html>

<head>
    <meta charset=utf-8>
    <title>Post</title>
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <link rel="stylesheet" type="text/css" href="post.css">
    <link rel="stylesheet" href="./css/font-awesome.min.css">
    <script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="./js/masonry-docs.min.js"></script>
    <script src="./js/Chart.bundle.js"></script>

</head>

<body>



    <div class="navi">

        <div class="title"><h style="line-height:60px;">Medical Assistance</h></div>
        <input id="search_input" class="search_input" type="text" name="search" placeholder="搜索"/>


        <div class="search_btn" onclick="search()">
            <i class="fa fa-search fa-lg" aria-hidden="true" style="color:white;margin-left:15px;line-height:60px;cursor:pointer;position:absolute;"></i>
        </div>

        <div class="username" onclick="showSignOut()">
            <?php
                echo $login;?>
        </div>
        <div class="menu">
            <div class='signout_btn_bg' onmouseover="this.className='signout_btn_mouseover_bg'" onmouseout="this.className='signout_btn_bg'" onclick="signOut()">
                <i class='fa fa-sign-out' aria-hidden='true' style='margin-top:7px;margin-left:2px;display:inline-block;color:#969696'></i>

            </div>
            <div style="text-align:center;color:#969696;margin-top:7px;font-family:'Josefin Sans';">
                SignOut
            </div>
        </div>
    </div>
    <div id="grid" class="container-fluid">


    </div>
    <div class="chart_box" id="chart_box">
        <div style="width:630px;height:350px;margin-left: auto;margin-right: auto;margin-top:30px;">
            <canvas id="myChart"></canvas>
        </div>
        <div id="close_chart_btn" class="close_chart_btn">
            Close
        </div>
    </div>
</body>

<script src="./js/jq_resize.js"></script>

<script>

    var isLogin = "<?=$isLogin?>";
    var name = "<?=$login?>";
    var index = 1;
    var isSearch = false;
    var flag = true;

    var $grid = $('#grid').masonry({
        itemSelector: '.grid-item',
        gutter: 20,
        isAnimated: true,
        isFitWidth: true
        });


    initPost();

    function initPost(){
        $.post("./api.php", {"method":"web.getPost", "last":0}, function (data, textStatus){
            addPost(data);
        });
    }

    function addPost(data){
        var dataObj = eval("("+data+")");
        var post_list = new Array();
        if(dataObj.obj.length == 0){
            flag = false;
        }
        else{
            $.each(dataObj.obj, function(i, item) {
                var post_cell = new Array();
                post_cell[0] = item.id;
                post_cell[1] = item.name;
                post_cell[2] = item.title;
                post_cell[3] = item.content;
                post_cell[4] = item.phytime;
                post_cell[5] = item.time;
                post_cell[6] = item.account;
                index = item.id;

                var $items = $("<div class='grid-item' id='item"+item.id+"' style='height:250px;'>\
                    <div class='post_title_box'>\
                        <div class='user_name_bg' style='background-color:"+getRandomColor()+"'>\
                        "+ item.name.substring(0,1) +"\
                        </div>\
                        <div class='post_username'>\
                            "+ item.name +"\
                        </div>\
                        <div class='triangle_border_right'>\
                        </div>\
                        <div class='post_title'>\
                            "+ item.title +"\
                        </div>\
                    </div>\
                    <div class='post_content_box'>\
                        <p class='post_content'>\
                            "+ item.content +"\
                        </p>\
                    </div>\
                    <div class='comment_data_box'>\
                    </div>\
                    <div class='comment_ctrl_box'>\
                        <div class='data_btn_bg' onmouseover=\"this.className='data_btn_mouseover_bg'\" onmouseout=\"this.className='data_btn_bg'\">\
                            <i class='fa fa-table' aria-hidden='true' style='margin-top:9px;margin-left:2px;color:#969696;'></i>\
                        </div>\
                        <div class='comment_btn_bg' onmouseover=\"this.className='comment_btn_mouseover_bg'\" onmouseout=\"this.className='comment_btn_bg'\">\
                            <i class='fa fa-commenting' aria-hidden='true' style='margin-top:7px;margin-left:2px;display:inline-block;color:#969696'></i>\
                        </div>\
                        <div class='comment_input_box'>\
                            <input class='comment_input' type='text' name='search' placeholder='发表评论...'/>\
                        </div>\
                    </div>\
                </div>");
                if((i == 0 && index == 1) || isSearch){
                    $grid.prepend( $items ).masonry( 'prepended', $items );
                    isSearch = false;
                }
                else{
                    $grid.append( $items ).masonry( 'appended', $items);
                }
                setCommentListener("#item"+item.id, item);
                setDataClickListener("#item"+item.id, item);
                $("#item"+item.id).height($("#item"+item.id+" .post_content_box").height() + 268);
            });
            flag = true;
        }

    }

    function setDataClickListener(itemid, item){
        $btn = $(itemid).children(".comment_ctrl_box").children(".data_btn_bg");
        $btn.on( 'click', function() {

            $.post("./api.php", {"method":"web.getChartData", "postID":item.id}, function (data, textStatus){
                var dataObj = eval("("+data+")");
                var labels = new Array();
                var data = new Array();
                var steps = new Array();
                if(dataObj.event == 0){
                    $.each(dataObj.obj, function(i, item) {
                        labels.push(item.scantime);
                        data.push(Number(item.heartrate));
                        steps.push(Number(item.steps));
                    });
                    changeChartData(labels, data, steps);
                }
                else{

                }
            });
        });

    }


    function setCommentListener(itemid, item){
        $btn = $(itemid).children(".comment_ctrl_box").children(".comment_btn_bg");
        $(itemid).resize(
            function(){
                $grid.masonry();
            });

        $btn.on( 'click', function() {
            if($(itemid+" .comment_input_box").width() != 320 && $(itemid+ " .comment_data_box").children().length == 0){
                $.post("./api.php", {"method":"web.getComment", "postID":item.id}, function (data, textStatus){
                    var dataObj = eval("("+data+")");
                    if(dataObj.event == 0){
                        $.each(dataObj.obj, function(i, item) {
                            insertComment(itemid, item.name, item.content);

                        });
                        $(itemid+ " .comment_data_box").toggle();

                        $(itemid).height($(itemid+" .post_content_box").height() + $(itemid+" .comment_data_box").height() + 268);
                        $(itemid +" .comment_data_box").toggle();
                    }
                });
            }
        });
        $btn.on( 'click', function() {
            if(isLogin == "1"){
                if($(itemid+" .comment_input_box").children(".comment_input").val() != ""){
                    addComment(itemid, item, $(itemid+" .comment_input_box").children(".comment_input").val());

                }
                else{
                    $(itemid+" .comment_input_box").width(320);
                }
            }
            else{
                alert("Please Sign In！");
            }

        });
    }

    var isInit = false;
    var config;

    function changeChartData(label, data, steps){
        if(!isInit){
            config = {
                type: 'line',
                data: {
                    labels: label,
                    datasets: [{
                        label: "HeartRate",
                        data: data,
                        borderWidth: 2,
                        Radius: 2,
                        pointRadius: 0,
                        pointHoverRadius: 6,
                        fill: false,
                        tension: 0.2,
                        yAxisID: "y-axis-1"
                    },
                    {
                        label: "Steps",
                        data: steps,
                        borderWidth: 2,
                        Radius: 2,
                        pointRadius: 0,
                        pointHoverRadius: 6,
                        fill: false,
                        tension: 0.2,
                        yAxisID: "y-axis-2"
                    }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,

                    scales: {
                        xAxes: [{
                            display: true,

                        }],
                        yAxes: [{
                            display: true,
                            ticks: {
                                Min: 40,
                                Max: 200,
                                beginAtZero:false,
                                stepSize:5,
                            },
                            position: "left",
                            id: "y-axis-1",

                        },
                        {
                            display: true,
                            ticks: {
                                Min: 0,
                                Max: 100000,
                                beginAtZero:true,
                                stepSize:1000,
                            },
                            position: "right",
                            id: "y-axis-2",

                        }]
                    }
                }
            };

            $.each(config.data.datasets, function(i, dataset) {

                dataset.backgroundColor = 'rgba(56, 191, 195, 1)';
                dataset.pointBorderColor = 'rgba(9, 169, 183, 1)';
                dataset.pointBackgroundColor = 'rgba(9, 169, 183, 1)';
                dataset.pointHoverBackgroundColor = 'rgba(255, 255, 255, 1)';
                dataset.pointBorderWidth = 2;
                dataset.pointHitRadius = 10;
            });
            config.data.datasets[0].borderColor = 'rgba(9, 169, 183, 1)';
            config.data.datasets[1].borderColor = 'rgba(255, 176, 128, 1)';
            config.data.datasets[1].backgroundColor = 'rgba(255, 176, 128, 1)';
            var ctx = document.getElementById("myChart");
            window.myChart = new Chart(ctx, config);
            isInit = true;
        }else{
            config.data.datasets.splice(0, 2);
            config.data.datasets.splice(1, 2);
            window.myChart.update();
            var newDataset1 = {
                label: "HeartRate",
                labels: label,
                data: data,
                borderWidth: 2,
                Radius: 2,
                pointRadius: 0,
                pointHoverRadius: 6,
                fill: false,
                tension: 0.2,
                yAxisID: "y-axis-1"
            };
            var newDataset2 = {
                    label: "Steps",
                    data: steps,
                    borderWidth: 2,
                    Radius: 2,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    fill: false,
                    tension: 0.2,
                    yAxisID: "y-axis-2"
            }
            ;
            $.each(newDataset1, function(i, dataset) {
                newDataset1.borderColor = 'rgba(9, 169, 183, 1)';
                newDataset1.backgroundColor = 'rgba(56, 191, 195, 1)';
                newDataset1.pointBorderColor = 'rgba(9, 169, 183, 1)';
                newDataset1.pointBackgroundColor = 'rgba(9, 169, 183, 1)';
                newDataset1.pointHoverBackgroundColor = 'rgba(255, 255, 255, 1)';
                newDataset1.pointBorderWidth = 2;
                newDataset1.pointHitRadius = 10;
            });
            $.each(newDataset2, function(i, dataset) {
                newDataset2.borderColor = 'rgba(255, 176, 128, 1)';
                newDataset2.backgroundColor = 'rgba(255, 176, 128, 1)';
                newDataset2.pointBorderColor = 'rgba(9, 169, 183, 1)';
                newDataset2.pointBackgroundColor = 'rgba(9, 169, 183, 1)';
                newDataset2.pointHoverBackgroundColor = 'rgba(255, 255, 255, 1)';
                newDataset2.pointBorderWidth = 2;
                newDataset2.pointHitRadius = 10;
            });

            config.data.datasets.push(newDataset1);
            config.data.datasets.push(newDataset2);
            window.myChart.update();
        }
        $("#chart_box").animate({opacity:"show"});
        $("#close_chart_btn").on( 'click', function() {
            $("#chart_box").animate({opacity:"hide"});
        });
    };


    function addComment(itemid, item, comment){
        var name = "<?=$login?>";
        var account = "<?=$account?>";
        if(isLogin == "1"){
            $.post("./api.php", {"method":"web.putPost", "postID":item.id, "account":account, "name":name, "comment":comment}, function (data, textStatus){
                var dataObj = eval("("+data+")");
                if(dataObj.event == 0){
                    insertComment(itemid, name, comment);
                }
                else{
                    alert("发送失败");
                }
            });
        }
    }

    function insertComment(itemid, name, content){
        $div = $(itemid).children(".comment_data_box");
        $div.append("<div class='comment_cell'><div class='comment_triangle_border_right'></div><div class='comment_username'>"+ name +"</div><div class='comment_cotent'>"+content+"</div></div>");
        $(itemid+ " .comment_data_box").toggle();
        $(itemid).height($(itemid+" .post_content_box").height() + $(itemid+" .comment_data_box").height() + 268);
        $(itemid +" .comment_data_box").toggle();
    }



    function getRandomColor(){
                var rgb='rgb('+Math.floor(Math.random()*255)+','
                         +Math.floor(Math.random()*255)+','
                         +Math.floor(Math.random()*255)+')';
                console.log(rgb);
                return rgb;
    }


    $(window).scroll(function(){

        var scrollTop = $(this).scrollTop();
           var scrollHeight = $(document).height();
           var windowHeight = $(this).height();
           if (scrollTop + windowHeight == scrollHeight && flag) {  //滚动到底部执行事件
               flag = false;
               $.post("./api.php", {"method":"web.getPost", "last":index}, function (data, textStatus){
                   addPost(data);
               });

           }
        }
     );



    function search(){

        if($("#search_input").val() != ""){
            $("#grid").empty();
            $.post("./api.php", {"method":"web.searchPost", "keywords":$("#search_input").val()}, function (data, textStatus){
                isSearch = true;
                addPost(data);
            });
        }
        else{
            isSearch = true;
            $("#grid").empty();
            initPost();
        }
    }

    function showSignOut(){
        if(isLogin){
             $(".menu").toggle();
        }
    }

    function signOut(){
        $.post("./api.php", {"method":"web.signOut"}, function (data, textStatus){
            location.href="index.html";
        });
    }

</script>
