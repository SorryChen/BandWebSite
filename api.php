<?php
$method = $_POST["method"];

try{
  $pdo = new PDO('mysql:host=localhost;dbname=Userinfo','root','root');
}
catch(Exception $e){
    echo("{'event': '100', 'msg': 'db error'}");
}

switch ($method) {
    case "user.loginByApp":{
        $sql = "select * from userinfo where (account='".$_POST["loginName"]."' and pwd='".$_POST["password"]."');";

        $result = $pdo->query($sql);
        $rows = $result->fetchAll();
        $rowCount = count($rows);

        if($rowCount != 0){
            echo json_encode(array('event'=>'0','msg'=>'login success'));
        }
		else {
            echo json_encode(array('event'=>'101','msg'=>'No such account or wrong password'));
        }

        break;
    }

	case "user.registerByPhone":{
        if($pdo->exec("INSERT INTO userinfo (account,pwd) VALUES('".$_POST["phoneNum"]."','".$_POST["password"]."');")){
            echo json_encode(array('event'=>'0','msg'=>'signup success'));
        }
        else {
            echo json_encode(array('event'=>'102','msg'=> 'name repeat'));
        }
        break;
    }

    case "user.getPersonalInfo":{
        $sql = "select * from userinfo where (account='".$_POST["loginName"]."' and pwd='".$_POST["password"]."');";
        $result = $pdo->query($sql);
        $rows = $result->fetchAll();
        $rowCount = count($rows);
        if($rowCount != 0){
            $sql = "select * from basicinfo where (account='".$_POST["loginName"]."');";
            $result = $pdo->query($sql);
            $rows = $result->fetchAll();
            $rowCount = count($rows);
            if($rowCount != 0){
                    $Reslt = array(
                        'sex' => $rows[0]["sex"],
                        'height' => $rows[0]["height"],
                        'weight' => $rows[0]["weight"],
                        'age' => $rows[0]["age"],
                        'name' => $rows[0]["name"],
                        'emergencyNumber' => $rows[0]["emergencyNumber"]);
                    echo json_encode(array('event'=>'0','msg'=>"success",'obj'=>$Reslt));

            }
            else {
                echo json_encode(array('event'=>'103','msg'=>'No record'));
            }
        }
        else {
            $responseValue = "pwd not match";
            echo json_encode(array('event'=>'101','msg'=>$responseValue));
        }
        break;
    }

    case "user.pushPersonalInfo":{
        if($pdo->exec("INSERT INTO basicinfo (account,sex,height,weight,age,name,emergencyNumber) VALUES('".$_POST["account"]."','".$_POST["sex"]."','".$_POST["height"]."','".$_POST["weight"]."','".$_POST["age"]."','".$_POST["name"]."','".$_POST["emergencyNumber"]."');")){
            echo json_encode(array('event'=>'0','msg'=>'push success'));
        }
        else{
            echo json_encode(array('event'=>'104','msg'=>'insert error'));
        }
        break;
    }

    case "user.pushPost":{
        $time = time();
        $postDate = date("Y-m-d");
        $s = $_POST["data"];
        $postObj = json_decode($s);

        $sql = "INSERT INTO post (account,postdate,title,content,clicknum,phytime) VALUES('".$postObj->account."','".$postDate."','".$postObj->title."','".$postObj->content."','0','".$time."');";

        if($pdo->exec($sql)){
            $signData = $postObj->signDataList;
            for($i = 0; $i < count($signData); $i++){

                $sql2 = "INSERT INTO phyinfo (account,time,heartrate,steps) VALUES('".$postObj->account."','".$time."','".$signData[$i]->heartRate."','".$signData[$i]->stepNum."');";

                if($pdo->exec($sql2)){

                }
                else {
                    echo json_encode(array('event'=>'104','msg'=>'insert phyinfo error'));
                    return;
                }
            }
            echo json_encode(array('event'=>'0','msg'=>'push success'));

        }
        else{
            echo json_encode(array('event'=>'104','msg'=>'insert post error'));
        }

        break;

    }

    case "web.getPost":{
        $lastIndex = $_POST["last"];
        if($lastIndex == 0){
            $sql = "select * from post order by ID desc limit 12;";
            $result = $pdo->query($sql);
            $rows = $result->fetchAll();
            $json = array();
            for ($i = 0; $i < count($rows); $i++) {
                $result = array(
                    'id' => $rows[$i]["ID"],
                    'name' => $rows[$i]["name"],
                    'title' => $rows[$i]["title"],
                    'content' => $rows[$i]["content"],
                    'phytime' => $rows[$i]["phytime"],
                    'time' => $rows[$i]["postdate"],
                    'account' => $rows[$i]["account"]);
                array_push($json, $result);
            }
            echo json_encode(array('event'=>'0','msg'=>"success",'obj'=>$json));
        }
        else{
            $sql = "select * from post where ID < '".$lastIndex."' order by ID desc limit 12;";
            $result = $pdo->query($sql);
            $rows = $result->fetchAll();
            $json = array();
            for ($i = 0; $i < count($rows); $i++) {
                $result = array(
                    'id' => $rows[$i]["ID"],
                    'name' => $rows[$i]["name"],
                    'title' => $rows[$i]["title"],
                    'content' => $rows[$i]["content"],
                    'phytime' => $rows[$i]["phytime"],
                    'time' => $rows[$i]["postdate"],
                    'account' => $rows[$i]["account"]);
                array_push($json, $result);
            }
            echo json_encode(array('event'=>'0','msg'=>"success",'obj'=>$json));
        }
        break;
    }

    case "web.getUserName":{
        $account = $_POST["account"];
        $sql = "select * from basicinfo where account='".$account."';";
        $result = $pdo->query($sql);
        $rows = $result->fetchAll();
        for ($i = 0; $i < count($rows); $i++) {
            $result = array(
                'name' => $rows[$i]["name"]
            );
            echo json_encode(array('event'=>'0','msg'=>"success",'obj'=>$result));
        }
        break;
    }

    case "web.login":{
        $username = $_POST['account'];
        $password = $_POST['password'];
        $sql = "select * from userinfo where account='".$username."' and pwd='".md5($password)."';";
        $result = $pdo->query($sql);
        $rows = $result->fetchAll();
        $rowCount = count($rows);

        //判断用户以及密码
        if($rowCount != 0)
        {
            session_start();
            $_SESSION['account'] = $rows[0]['account'];
            header("Location: main.php");
            exit;


        }else{
            echo $username." ".$password;
        }
        break;
    }

    case "web.putPost":{
        $postID = $_POST['postID'];
        $account = $_POST['account'];
        $name = $_POST['name'];
        $comment = $_POST['comment'];
        //$comment = str_replace("'", "/\'", $comment);
        $time = date("Y-m-d G:i:s");

        $query="INSERT INTO comment (name,account,postID,content,date) VALUES(:name,:account,:postID,:comment,:time);";
        $result=$pdo->prepare($query);
        $result->execute(
            array(':name'=>$name,
                    ':account'=>$account,
                    ':postID'=>$postID,
                    ':comment'=>$comment,
                    ':time'=>$time,));  // 执行一次

        if($result){
            echo json_encode(array('event'=>'0','msg'=>'push success'));

        }
        else{
            echo json_encode(array('event'=>'104','msg'=>'insert comment error'.$sql));
        }
        break;
    }

    case "web.getComment":{
        $postID = $_POST['postID'];
        $sql = "select * from comment where postID='".$postID."';";
        $result = $pdo->query($sql);
        $rows = $result->fetchAll();
        $json = array();
        if(count($rows) != 0){
            for ($i = 0; $i < count($rows); $i++) {
                $result = array(
                    'name' => $rows[$i]["name"],
                    'content' => $rows[$i]["content"]
                );
                array_push($json,$result);

            }
            echo json_encode(array('event'=>'0','msg'=>"success",'obj'=>$json));
        }
        else{
            echo json_encode(array('event'=>'103','msg'=>"No Record"));
        }
        break;
    }

    case "web.getChartData":{

        $postID = $_POST['postID'];
        $sql = "select * from phyinfo where time=(select phytime from post where ID='".$postID."');";
        $result = $pdo->query($sql);
        $rows = $result->fetchAll();
        $json = array();
        if(count($rows) != 0){
            for ($i = 0; $i < count($rows); $i++) {
                $result = array(
                    'scantime' => $rows[$i]["scantime"],
                    'heartrate' => $rows[$i]["heartrate"],
                    'steps' => $rows[$i]["steps"]
                );
                array_push($json,$result);

            }
            echo json_encode(array('event'=>'0','msg'=>"success",'obj'=>$json));
        }
        else{
            echo json_encode(array('event'=>'103','msg'=>"No Record"));
        }
        break;
    }

    case "web.searchPost":{
        $keywords = explode(" ", $_POST['keywords']);
        $json = array();
        foreach ($keywords as $key => $value) {
            $sql = "select * from post where (title like '%".$value."%') or (content like '%".$value."%')";
            $result = $pdo->query($sql);
            $rows = $result->fetchAll();

            for ($i = 0; $i < count($rows); $i++) {
                $result = array(
                    'id' => $rows[$i]["ID"],
                    'name' => $rows[$i]["name"],
                    'title' => $rows[$i]["title"],
                    'content' => $rows[$i]["content"],
                    'phytime' => $rows[$i]["phytime"]);
                array_push($json, $result);
            }
        }
        echo json_encode(array('event'=>'0','msg'=>"success",'obj'=>$json));
        break;
    }

    case "web.signOut":{
        session_start();    //启动会话
        session_unset();    //删除会话
        session_destroy();  //结束会话
        header("location: index.html");
        break;
    }

    case "web.getMyPost":{
        $lastIndex = $_POST["last"];
        $account = $_POST["account"];

        if($lastIndex == 1){
            $sql = "select * from post where account='".$account."' order by ID desc limit 12;";

            $result = $pdo->query($sql);
            $rows = $result->fetchAll();
            $json = array();
            for ($i = 0; $i < count($rows); $i++) {
                $result = array(
                    'id' => $rows[$i]["ID"],
                    'name' => $rows[$i]["name"],
                    'title' => $rows[$i]["title"],
                    'content' => $rows[$i]["content"],
                    'phytime' => $rows[$i]["phytime"],
                    'time' => $rows[$i]["postdate"],
                    'account' => $rows[$i]["account"]);
                array_push($json, $result);
            }
            echo json_encode(array('event'=>'0','msg'=>"success",'obj'=>$json));
        }
        else{
            $sql = "select * from post where ID < '".$lastIndex."' and account='".$account."' order by ID desc limit 12;";
            $result = $pdo->query($sql);
            $rows = $result->fetchAll();
            $json = array();
            for ($i = 0; $i < count($rows); $i++) {
                $result = array(
                    'id' => $rows[$i]["ID"],
                    'name' => $rows[$i]["name"],
                    'title' => $rows[$i]["title"],
                    'content' => $rows[$i]["content"],
                    'phytime' => $rows[$i]["phytime"],
                    'time' => $rows[$i]["postdate"],
                    'account' => $rows[$i]["account"]);
                array_push($json, $result);
            }
            echo json_encode(array('event'=>'0','msg'=>"success",'obj'=>$json));
        }
        break;
    }
}
