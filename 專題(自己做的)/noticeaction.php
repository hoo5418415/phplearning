<?php
if(isset($_POST['YES'])) {
    session_start();
    $id = $_SESSION['id'];
    //拿學號近來
    $classid = $_SESSION['deleteclass'];
    $dbhost = '127.0.0.1';
    $dbuser = 'hj';
    $dbpass = 'test1234';
    $dbname = '選課系統';
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass) or die('Error with MySQL connection');
    mysqli_query($conn,"SET NAMES utf8");
    mysqli_select_db($conn, $dbname);
    //連接資料庫
    $sql ="DELETE from 學生已選課程 where 課程代碼 = '$classid' and 學號 = '$id';";
    $result = mysqli_query($conn,$sql) or die('退選錯誤');
    echo "成功退選<p>";
    echo "5秒後自動跳轉至選課介面";
    header("Refresh:5;url=chooseclass.php");
    exit();
 }
 if(isset($_POST['NO'])) {
    echo "5秒後自動跳轉至選課介面<p>";
    header("Refresh:5;url=chooseclass.php");
    exit();
 }
?>