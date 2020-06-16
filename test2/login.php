<?php
    include('import.php');
    if($session_id!=$post_id&&$post_id!=-2){
        $studentID=$_POST["text"];
        $submit=$_POST["submit"];
        $_SESSION["studentID"]=$studentID;
        $result=myquery($connection,"SELECT * FROM 學生 WHERE 學號 = '$studentID';");
        if($result->num_rows>0){
            if($submit=="sign up"){//已註冊
                $reload_page_state="registered";
            }else{//登入
                myheader("select_course.php",0);
            }
        }elseif(!empty($studentID)){
            if($submit=="sign up"){//註冊
                myheader("sign_up.php",0);
            }else{//尚未註冊
                $reload_page_state="no register";
            }
        }else{
            $reload_page_state="no text";
        }
        $_SESSION["session_id"]=$post_id;
    }else{
        $reload_page_state="no process";
    }
?>
<form method="post" action="">
    輸入學號: <input type="text" name="text">
    <input type="text" hidden="hidden" name="post_id" value="<?php echo rand(1, 999999); ?>">
    <input type="submit" name="submit" value="sign up">
    <input type="submit" name="submit" value="login">
</form>
<?php
    switch($reload_page_state){
        case "registered":
            echo "已註冊";
            break;
        case "no register":
            echo "未註冊";
            break;
        case "no text":
            echo "無輸入";
            break;
        default:
    }
?>