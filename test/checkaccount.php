<?php
   if(isset($_POST['login'])) {
    $id=$_POST["id"];
    $dbhost = '127.0.0.1';
	$dbuser = 'hj';
	$dbpass = 'test1234';
    $dbname = '選課系統';
	$conn = mysqli_connect($dbhost, $dbuser, $dbpass) or die('Error with MySQL connection');
	mysqli_query($conn,"SET NAMES utf8");
	mysqli_select_db($conn, $dbname);
    $sql = "SELECT 學號 FROM 學生 where 學號 = '$id';";
    $result = mysqli_query($conn,$sql) or die('fail query');
    if($result == true){
        session_start();
        $_SESSION['id']=$id;
        header('Location:chooseclass.php');
        exit();
    }
    }
    if(isset($_POST['signup'])){
        header('Location:signup.php');
        exit();
    }
?>