<?php

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

</head>

<body>
    <div id="grid" class="container-fluid">
        

    </div>

</body>

<script src="./js/jq_resize.js"></script>

<script>

    var isLogin = "1";
    var name = getQueryString("name");
    var account = getQueryString("account");
    var index = 1;
    var flag = true;

    var $grid = $('#grid').masonry({
        itemSelector: '.grid-item',
        gutter: 20,
        isAnimated: true,
        isFitWidth: true
    });

    getMyPost();


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

        });
    }

    function addComment(itemid, item, comment){
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
               $.post("./api.php", {"method":"web.getMyPost", "last":index, "account":account}, function (data, textStatus){
                   addMyPost(data);
               });

           }
        }
    );

    var index = 0;

    function getMyPost(){

        $.post("./api.php", {"method":"web.getMyPost", "last":index, "account":account}, function (data, textStatus){
            addMyPost(data);
        });
    }

    function addMyPost(data){
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
                        <div class='comment_btn_bg' onmouseover=\"this.className='comment_btn_mouseover_bg'\" onmouseout=\"this.className='comment_btn_bg'\">\
                            <i class='fa fa-commenting' aria-hidden='true' style='margin-top:7px;margin-left:2px;display:inline-block;color:#969696'></i>\
                        </div>\
                        <div class='comment_input_box'>\
                            <input class='comment_input' type='text' name='search' placeholder='发表评论...'/>\
                        </div>\
                    </div>\
                </div>");
                if((i == 0 && index == 1)){
                    $grid.prepend( $items ).masonry( 'prepended', $items );
                    isSearch = false;
                }
                else{
                    $grid.append( $items ).masonry( 'appended', $items);
                }
                setCommentListener("#item"+item.id, item);
                $("#item"+item.id).height($("#item"+item.id+" .post_content_box").height() + 268);
            });
            flag = true;
        }
    }


    function getQueryString(name)
    {
         var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
         var r = window.location.search.substr(1).match(reg);
         if(r!=null)return  unescape(r[2]); return null;
    }


</script>
