<link rel="stylesheet" type="text/css" href="style.css">
<?php
    session_start();
    require('function.php');
    $connection = new mysqli("localhost", "hj", "test1234", "選課系統");
    if ($connection->connect_error) {
        die("連線失敗：" . $connection->connect_error);
    }
    $session_id = empty($_SESSION["session_id"]) ? -1 : $_SESSION["session_id"];
    $post_id = empty($_POST["post_id"]) ? -2 : $_POST["post_id"];
    //echo "session_id:$session_id post_id:$post_id";
?>