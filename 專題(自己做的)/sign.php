<?php
    $id = $_POST['id'];
    $name = $_POST['name'];
    $classid = $_POST['classid'];
    $score = "0";
    $dbhost = '127.0.0.1';
    $dbuser = 'hj';
    $dbpass = 'test1234';
    $dbname = '選課系統';
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass) or die('Error with MySQL connection');
    mysqli_query($conn,"SET NAMES 'utf8'");
    mysqli_select_db($conn,$dbname);
    $sql = "INSERT INTO 學生(學號,姓名,總學分,班級編號)VALUES('$id','$name','$score','$classid');";
    $result = mysqli_query($conn,$sql) or die('新增異常');
    //上面是先新增學號
    $sql1 = "SELECT `課程代碼` FROM `課程` natural join 班級的課程 where 班級編號 = '$classid' and 必選修 = 'M';";
    $result1 = mysqli_query($conn,$sql1) or die('查詢失敗');
    //上面是查詢班級代號=輸入的班級代號且為必修
    while ($row = mysqli_fetch_array($result1,MYSQLI_ASSOC)) {
        $must = $row['課程代碼'];
        $sql2 = "INSERT INTO 學生已選課程(學號,課程代碼) values('$id','$must');";
        $result2 = mysqli_query($conn,$sql2) or die('新增必修失敗');
    }
    //新增必修至學生已選課程

    if ($result2 == true){
        echo "成功新增帳號<br>";
        echo "5秒後跳轉至登入頁面";
        header("Refresh:5;url=index.php");
        exit();
    }

    //成功新增ID，且必修自動加成功

?>