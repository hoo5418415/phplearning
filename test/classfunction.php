
<?php
  $dbhost = '127.0.0.1';
  $dbuser = 'hj';
  $dbpass = 'test1234';
  $dbname = '選課系統';
  $conn = mysqli_connect($dbhost, $dbuser, $dbpass) or die('Error with MySQL connection');
  mysqli_query($conn,"SET NAMES utf8");
  mysqli_select_db($conn, $dbname);
  //連接資料庫
  session_start();
  $id = $_SESSION['id'];
  //把id丟進來
  if(isset($_POST['Signout'])) {
    header('Location:index.php');
    exit();
  }
  //登出
//-----------------------------------------------------------------
//-----------------------------------------------------------------
  if(isset($_POST['SubmitNewclass'])) {
    $classid = $_POST['newclass'];
    //傳入要選的課的課程編號
//--------------------------------------------------------------------------------------
    $sql = "Select 課程代碼 from 課程 where 課程代碼 = '$classid';";
    $result = mysqli_query($conn,$sql) or die("無此課程，請重新輸入<p>");
    if (mysqli_query($conn,$sql) == false){
      echo "5秒後自動跳回選課介面";
      header("Refresh:5;url=chooseclass.php");
      exit();
    }
    //防小白裝置,怕有人輸入沒有的課程
//---------------------------------------------------------------------------------------------    
    $sql = "SELECT 開課人數,已收授人數 FROM 課程 where 課程代碼 = '$classid';";
    $result = mysqli_query($conn,$sql) or die('額滿檢查錯誤');
    while($row = mysqli_fetch_array($result)){
      $expected=$row['開課人數'];
      $in = $row['已收授人數'];
    }
    if ($expected <= $in){
      echo "該課程已額滿，請選擇其他課程<p>";
      echo "5秒後自動跳回選課介面";
      header("Refresh:5;url=chooseclass.php");
      exit();
    }
    //上面判斷是否額滿
//-----------------------------------------------------------------------------------------  
    $sql ="SELECT 星期,節 from 課程時間 where 課程代碼 = '$classid';";
    $result = mysqli_query($conn,$sql) or die('衝堂_獲取要選課程時間錯誤');
    while($row = mysqli_fetch_array($result)){
      $day = $row['星期'];
      $time = $row['節'];
    }
    //獲取要選的課的星期和節
    $sql = "SELECT 課程代碼 FROM 學生已選課程 where 學號 = '$id';";
    $result = mysqli_query($conn,$sql) or die('衝堂_獲取已選課程代碼錯誤');
    while($row = mysqli_fetch_array($result)){
      $courseid = $row['課程代碼'];
      //獲取以選課課程的課程代碼
      $sql2 = "SELECT 星期,節 from 課程時間 where 課程代碼 = '$courseid';";
      $result2 = mysqli_query($conn,$sql2) or die('衝堂_獲取已選課程時間錯誤');
      //拿以選課的選課代碼去找時間
      while($row = mysqli_fetch_array($result2)){
        $compareday = $row['星期'];
        $comparetime = $row['節'];
        if($compareday == $day && $comparetime == $time){
          echo "衝堂了，請重新操作<p>";
          echo "5秒後自動跳轉至選課介面";
          header("Refresh:5;url=chooseclass.php");
          exit();
        }
      }
    }
  //以上判斷是否衝堂
//-----------------------------------------------------------------------------------------------

    $sql ="SELECT 課程名稱 from 課程 where 課程代碼 = '$classid';";
    $result = mysqli_query($conn,$sql) or die('同名_獲取要選的課名錯誤');
    while($row = mysqli_fetch_array($result)){
      $classname = $row['課程名稱'];  
    }
    //獲取要選的課的課程名稱
    $sql = "SELECT 課程代碼 FROM 學生已選課程 where 學號 = '$id';";
    $result = mysqli_query($conn,$sql) or die('同名_獲取已選課程代碼錯誤');
    while($row = mysqli_fetch_array($result)){
      $courseid = $row['課程代碼'];
      //獲取以選課課程的課程代碼
      $sql2 = "SELECT 課程名稱 from 課程 where 課程代碼 = '$courseid';";
      $result2 = mysqli_query($conn,$sql2) or die('同名_獲取已選課的課程名稱錯誤');
      while($row = mysqli_fetch_array($result2)){
        $comparename = $row['課程名稱'];
        if($comparename == $classname){
          echo "你選取了同名課程<p>";
          echo "5秒後自動跳轉至選課介面";
          header("Refresh:5;url=chooseclass.php");
          exit();
        }
      }
    }
    //判斷同名課程
//--------------------------------------------------------------------------------------------
    $totalcredit= 0;
    //紀錄總學分
    $sql ="SELECT 學分數 from 課程 where 課程代碼 = '$classid';";
    $result = mysqli_query($conn,$sql) or die('超過30學分_獲取選的課的學分錯誤');
    while($row = mysqli_fetch_array($result)){
      $credit = $row['學分數'];
    }
    //獲取要選的課的學分數
    $sql = "SELECT 課程代碼 FROM 學生已選課程 where 學號 = '$id';";
    $result = mysqli_query($conn,$sql) or die('超過30學分_獲取已選課程代碼錯誤');
    while($row = mysqli_fetch_array($result)){
      $courseid = $row['課程代碼'];
      //獲取以選課課程的課程代碼
      $sql2 = "SELECT 學分數 from 課程 where 課程代碼 = '$courseid';";
      $result2 = mysqli_query($conn,$sql2) or die('超過30學分_計算總學分錯誤');
      while($row = mysqli_fetch_array($result2)){
        $classcredit = $row['學分數'];
        $totalcredit = $totalcredit + $classcredit;
      }
    }
    //算已選的課的學分
    if ($totalcredit + $credit > 30){
      echo "你超修了<p>";
      echo "5秒後自動跳轉至選課介面";
      header("Refresh:5;url=chooseclass.php");
      exit();
    }
    //判斷有沒有超過30學分
//-------------------------------------------------------------------------------------------
    $sql = "INSERT into 學生已選課程(學號,課程代碼)values('$id','$classid');";
    $result = mysqli_query($conn,$sql) or die('新增課程_新增至已選課程錯誤');
    echo "成功加選<p>";
    echo "5秒後自動跳轉至選課介面";
    header("Refresh:5;url=chooseclass.php");
    exit();
  }


  if(isset($_POST['SubmitDeleteclass'])) {
    $classid = $_POST['deleteclass'];
    //拿到要刪的課的課程代碼
    $totalcredit= 0;
    //紀錄總學分
//-----------------------------------------------------------------------------------------
    $checkhavechosen = 0;
    $sql ="SELECT 課程代碼 from 課程 where 課程代碼 = '$classid';";
    $result = mysqli_query($conn,$sql) or die('退選_判斷課程存在錯誤');
    while($row = mysqli_fetch_array($result)){
      $checkclassid = $row['課程代碼'];
      if($checkclassid == $classid){
        $checkhavechosen = $checkhavechosen + 1;
      }
    }
    if($checkhavechosen == 0){
      echo "你並未選擇此課程過<p>";
      echo "5秒後自動跳轉至選課介面";
      header("Refresh:5;url=chooseclass.php");
      exit();
    }
    //防小白裝置，防止有人拿還沒選過的課來退選
//-------------------------------------------------------------------------
    $sql ="SELECT 學分數 from 課程 where 課程代碼 = '$classid';";
    $result = mysqli_query($conn,$sql) or die('退選_獲取要刪的課程學分數錯誤');
    while($row = mysqli_fetch_array($result)){
      $credit = $row['學分數'];
    }
    //獲取要退的課的學分數
    $sql = "SELECT 課程代碼 FROM 學生已選課程 where 學號 = '$id';";
    $result = mysqli_query($conn,$sql) or die('退選_獲取已選課的課程代號錯誤');
    while($row = mysqli_fetch_array($result)){
      $courseid = $row['課程代碼'];
      //獲取以選課課程的課程代碼
      $sql2 = "SELECT 學分數 from 課程 where 課程代碼 = '$courseid';";
      $result2 = mysqli_query($conn,$sql2) or die('退選_獲取已選課的學分數錯誤');
      while($row = mysqli_fetch_array($result2)){
        $classcredit = $row['學分數'];
        $totalcredit = $totalcredit + $classcredit;
      }
    }
    //算總學分
    if($totalcredit - $credit < 9 ){
      echo "退完之後少於9學分<p>";
      echo "5秒後自動跳轉至選課介面";
      header("Refresh:5;url=chooseclass.php");
      exit();
    }
    //判斷退了以後有沒有低於9學分
//--------------------------------------------------------------------------------------------
    $sql ="SELECT 必選修 from 課程 where 課程代碼 = '$classid';";
    $result = mysqli_query($conn,$sql) or die('退選_獲取要刪的課程必選修錯誤');
    while($row = mysqli_fetch_array($result)){
      $mustoroption= $row['必選修'];
    }
    if ($mustoroption == 'M'){
      $_SESSION['deleteclass']=$classid;
      header('Location:noticepage.php');
      exit();
    }
    //判斷退的是不是必修，如果是轉去提醒頁
//-------------------------------------------------------------------
  $sql ="DELETE from 學生已選課程 where 課程代碼 = '$classid' and 學號 = '$id'";
  $result = mysqli_query($conn,$sql) or die('退選_退選錯誤');
  echo "成功退選<p>";
  echo "5秒後自動跳轉至選課介面";
      header("Refresh:5;url=chooseclass.php");
      exit();
  }
  //退選
  ?>