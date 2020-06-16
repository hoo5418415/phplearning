<?php
    include('import.php');
    if($session_id!=$post_id&&$post_id!=-2){
        $name=$_POST["text"];
        if(!empty($name)){
            $class=$_POST["select"];
            $studentID=$_SESSION["studentID"];
            myquery($connection,"INSERT INTO 學生 VALUES ('$studentID','$name',0,'$class');");
            myheader("login.php",0);
        }else{
            $error=1;
        }
        $_SESSION["session_id"]=$post_id;
    }
?>
<form method="post" action="">
    輸入姓名: <input type="text" name="text">
    <select name="select">
<?php
$result=myquery($connection,"SELECT 班級編號,班級名稱 FROM 班級;");
    while ($row = $result->fetch_assoc()) {
        if(!preg_match("/\通識/i", $row["班級名稱"])){
            echo '<option value="'.$row["班級編號"].'">'.$row["班級名稱"].'</option>';
        }
    } 
?>
    </select>
    <input type="submit" value="sign up">
    <input type="text" hidden="hidden" name="post_id" value="<?php echo rand(1, 999999); ?>">
</form> 
<?php
    if(!empty($error)){
        echo "未輸入名子";
    }
?>
