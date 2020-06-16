<?php
  session_start();
  $id = $_SESSION['id'];
  echo "你好 $id<p>";
  echo "以下是你已選擇的課程<p>";
  echo "課程代碼 課程名稱 必選修 星期 節<p>";
  //網頁上的訊息
  $totalcredit = 0;
  //紀錄總學分
  $dbhost = '127.0.0.1';
  $dbuser = 'hj';
  $dbpass = 'test1234';
  $dbname = '選課系統';
  $conn = mysqli_connect($dbhost, $dbuser, $dbpass) or die('Error with MySQL connection');
  mysqli_query($conn,"SET NAMES utf8");
  mysqli_select_db($conn, $dbname);
  //連資料庫
  $sql = "SELECT 課程代碼 FROM 學生已選課程 where 學號 = '$id';";
  $result = mysqli_query($conn,$sql) or die('fail query');
  while($row = mysqli_fetch_array($result)){
    $tmpclassid=$row['課程代碼'];
    //拿到已選課的課程代碼
    $sql2 = "SELECT 課程代碼,課程名稱,必選修,學分數 from 課程 where 課程代碼 = '$tmpclassid';";
    $result2 = mysqli_query($conn,$sql2) or die('fail query');
    while($row2 = mysqli_fetch_array($result2)){
      echo $row2['課程代碼'];
      echo "     ";
      echo $row2['課程名稱'];
      echo "     ";
      echo $row2['必選修'];
      echo "     ";
      $tmp = $row2['學分數'];
      $totalcredit = $totalcredit + $tmp;
    }
    $sql2 = "SELECT 星期,節 from 課程時間 where 課程代碼 = '$tmpclassid';";
    $result2 = mysqli_query($conn,$sql2) or die('fail query');
    while($row2 = mysqli_fetch_array($result2)){
      echo $row2 ['星期'];
      echo " ";
      echo $row2 ['節'];
      echo " ";
    }
    echo "<p>";
  }
  echo "總學分為";
  echo $totalcredit;
  echo "<p>";
?>

<HTML>
  <body>
  <form method="POST" action=classfunction.php>
    <h3>如果需要新增課程,請在下面輸入欄中填上課程代碼</h3>
    <input type="text" name="newclass">
    <INPUT TYPE=submit VALUE=新增 name=SubmitNewclass>
    <p>
    <h3>如果需要退出課程,請在下面輸入欄填上想退出課程的課程代碼</h3>
    <input type="text" name="deleteclass">
    <INPUT TYPE=submit VALUE=刪除 name=SubmitDeleteclass>
    <p>
    <h3>如果想要登出，請按下面按鈕</h3>
    <INPUT TYPE=submit VALUE=登出 name=Signout>
  </body>
</HTML>
  