<body class="c">
<?php
    include('import.php');
    $studentID=$_SESSION["studentID"];

    $result=myquery($connection,"SELECT * FROM 學生已選課程 WHERE 學號 = '$studentID';");
    if($result->num_rows==0){//如果還沒選課
        $sql = "
            SELECT * FROM 班級的課程 NATURAL JOIN 課程
            WHERE 班級編號 = (SELECT 班級編號 FROM 學生 WHERE 學號='$studentID') AND 必選修='M';
            ";
        $result=myquery($connection,$sql);//加入必修
        while ($row = $result->fetch_assoc()) {
            $courseID=$row["課程代碼"];
            myquery($connection,"UPDATE 課程 SET 已收授人數=已收授人數+1 WHERE 課程代碼=$courseID;");
            myquery($connection,"INSERT INTO 學生已選課程 (學號,課程代碼) VALUES ('$studentID',$courseID);");
        }
    }
    #更新學生總學分
    $sql="SELECT SUM(學分數) FROM (SELECT 課程代碼 FROM 學生已選課程 WHERE 學號='$studentID') as a NATURAL JOIN 課程;";
    $result_credit_total=myquery($connection,$sql);
    $row=$result_credit_total->fetch_assoc();
    $credit_total=$row["SUM(學分數)"];
    myquery($connection,"UPDATE 學生 SET 總學分=$credit_total WHERE 學號='$studentID';");

    if($session_id!=$post_id&&$post_id!=-2){
        $post_form=empty($_POST["post_form"])?-1:$_POST["post_form"];
        switch($post_form){
            case "course name search":
                $text=$_POST["text"];
                if(!empty($text)){
                    $sql="SELECT * FROM 課程 WHERE 課程名稱 LIKE '%$text%';";
                    $result_course=myquery($connection,$sql);
                    $_SESSION["tmp_search_course_sql"]=$sql;
                    $reload_page_state["search"]="display";
                }else{
                    $reload_page_state["search"]="no text";
                }
                break;
            case "course id search":
                $text=$_POST["text"];
                if(!empty($text)&&is_numeric($text)){
                    $sql="SELECT * FROM 課程 WHERE 課程代碼 = $text;";
                    $result_course=myquery($connection,$sql);
                    $_SESSION["tmp_search_course_sql"]=$sql;
                    $reload_page_state["search"]="display";
                }elseif(!empty($text)){
                    $reload_page_state["search"]="is string";
                }else{
                    $reload_page_state["search"]="no text";
                }
                break;
            case "take course by search result":
                $course_selection_state="take course";
                $text=$_POST["text"];
                break;
            case "drop course by search result":
                $course_selection_state="drop course";
                $text=$_POST["text"];
                break;
            case "drop_course_compulsory":
                if($_POST["submit"]=="Yes"){
                    $text=$_POST["text"];
                    myquery($connection,"UPDATE 課程 SET 已收授人數=已收授人數-1 WHERE 課程代碼=$text;");
                    myquery($connection,"DELETE FROM 學生已選課程 WHERE 學號='$studentID' AND 課程代碼=$text;");
                    $reload_page_state["drop_course"]="success";
                }
                break;
            case "login_out":
                if($credit_total<=30&&$credit_total>=9){
                    unset($studentID);
                    myheader("login.php",0);
                }
                break;
            case "course time search":
                $day=$_POST["day"];
                $time=$_POST["time"];
                $_SESSION["tmp_day"]=$day;
                $_SESSION["tmp_time"]=$time;
                $sql="
                    SELECT * FROM 課程 WHERE 課程代碼 IN (SELECT 課程代碼 FROM 課程時間 WHERE 星期='$day' AND 節=$time);
                    ";
                $result_course=myquery($connection,$sql);
                $_SESSION["tmp_search_course_sql"]=$sql;
                $reload_page_state["search"]="display";
                break;
            default:
        }
        if(!empty($course_selection_state)){
            if($course_selection_state=="take course"){
                $result_course_full=myquery($connection,"SELECT * FROM 課程 WHERE 課程代碼 = $text AND 開課人數 <= 已收授人數;");
                $sql="
                    SELECT * FROM (SELECT 課程代碼 FROM 學生已選課程 WHERE 學號='$studentID') as a NATURAL JOIN 課程時間 NATURAL JOIN 
                    (SELECT 星期,節 FROM 課程時間 WHERE 課程代碼=$text) as b;
                    ";
                $result_course_conflict=myquery($connection,$sql);
                $sql="
                    SELECT * FROM (SELECT 課程代碼 FROM 學生已選課程 WHERE 學號='$studentID') as a NATURAL JOIN 課程
                    WHERE 課程名稱 IN (SELECT 課程名稱 FROM 課程 WHERE 課程代碼 = $text);
                    ";
                $result_course_same=myquery($connection,$sql);
                //print_query($result_course_conflict);
                $course_credit=myquery($connection,"SELECT 學分數 FROM 課程 WHERE 課程代碼=$text;");
                $course_credit=$course_credit->fetch_assoc();
                $course_credit=$course_credit["學分數"];
                $error=0;
                if($result_course_full->num_rows>0){
                    $take_course_error[$error++]="course_full";
                }
                if($result_course_conflict->num_rows>0){
                    $take_course_error[$error++]="course_conflict";
                }
                if($result_course_same->num_rows>0){
                    $take_course_error[$error++]="course_same";
                }
                if($credit_total+$course_credit>30){
                    $take_course_credit_over=$credit_total+$course_credit;
                    $take_course_error[$error++]="credit_over";
                }
                if($error>0){
                    $reload_page_state["take_course"]="failure";
                }else{
                    $reload_page_state["take_course"]="success";
                    myquery($connection,"UPDATE 課程 SET 已收授人數=已收授人數+1 WHERE 課程代碼=$text;");
                    myquery($connection,"INSERT INTO 學生已選課程 (學號,課程代碼) VALUES ('$studentID',$text);");
                }
            }else{
                $course_compulsory=myquery($connection,"SELECT * FROM 課程 WHERE 課程代碼=$text AND 必選修='M';");
                $course_credit=myquery($connection,"SELECT 學分數 FROM 課程 WHERE 課程代碼=$text;");
                $course_credit=$course_credit->fetch_assoc();
                $course_credit=$course_credit["學分數"];

                if($course_compulsory->num_rows>0){
                    $reload_page_state["drop_course"]="have_problem";
                    $drop_course_compulsory=$text;
                }
                if($credit_total-$course_credit<9){
                    $reload_page_state["drop_course"]="failure";
                    $drop_course_credit_under=$credit_total-$course_credit;
                }
                if(empty($reload_page_state["drop_course"])){
                    $reload_page_state["drop_course"]="success";
                    myquery($connection,"UPDATE 課程 SET 已收授人數=已收授人數-1 WHERE 課程代碼=$text;");
                    myquery($connection,"DELETE FROM 學生已選課程 WHERE 學號='$studentID' AND 課程代碼=$text;");
                }
            }
        }
        $state_refresh=0;
        $_SESSION["session_id"]=$post_id;
    }else{
        unset($_SESSION["tmp_day"]);
        unset($_SESSION["tmp_time"]);
    }
?>
<div style="float:left;width:100%;height:100%;">
    <div style="float:left;width:40%"><!--課表(start)-->
<?php
    for($r=0;$r<15;$r++){
        for($c=0;$c<6;$c++){
            $course_table[$r][$c]=0;
        }
    }
    $result_courseID=myquery($connection,"SELECT 課程代碼 FROM 學生已選課程 WHERE 學號 = '$studentID';");
    while ($row = $result_courseID->fetch_assoc()){//找每筆課程代碼的時間
        $courseID=$row["課程代碼"];
        $result_courseTime=myquery($connection,"SELECT * FROM 課程時間 WHERE 課程代碼 = '$courseID';");
        while ($row2 = $result_courseTime->fetch_assoc()){
            $course_table[$row2["節"]][dayToNum($row2["星期"])]=$courseID;//課程代碼放入course_table陣列
        }
    }
    for($r=0;$r<15;$r++){//課表
        if($r==0){//標題
            echo '
                <table class="course_table" style="width:100%;">
                    <tr>
                        <td></td>
                        <td style="width:18%">一</td>
                        <td style="width:18%">二</td>
                        <td style="width:18%">三</td>
                        <td style="width:18%">四</td>
                        <td style="width:18%">五</td>
                    </tr>
                ';
        }
        for($c=0;$c<6;$c++){
            if($r!=0 && $c==0){
                echo '
                    <tr>
                        <td class="b">'.$r.'</td>
                    ';
            } elseif($r!=0) {
                $courseID=$course_table[$r][$c];//課程代碼表
                    if($courseID!=0){
                        $result=myquery($connection,"SELECT * FROM 課程 WHERE 課程代碼 = '$courseID';");
                        $result=$result->fetch_assoc();
                        $courseName=$result["課程名稱"];
                        $courseID=$result["課程代碼"];
                        $teacherID=$result["教師ID"];
                        $result=myquery($connection,"SELECT 教師名稱 FROM 教師 WHERE 教師ID = '$teacherID';");
                        $result=$result->fetch_assoc();
                        $teacherName=$result["教師名稱"];
                        echo '<td>'.$courseName.'<br>'.$courseID.'<br>'.$teacherName.'</td>';
                    }else{
                        echo '<td></td>';
                    }
            }
        }
        echo '</tr>';
    }
    echo '</table>';
?>
<div style="float:left;border: 1px solid black;margin:10px 0px;width:100%;box-sizing: border-box;padding:10px">
    <!-- (s)學生資訊與登出 -->
    <div style="float:left;padding:10px 0px;width:100%;text-align:center;">
<?php
    $sql="SELECT SUM(學分數) FROM (SELECT 課程代碼 FROM 學生已選課程 WHERE 學號='$studentID') as a NATURAL JOIN 課程;";
    $result_credit_total=myquery($connection,$sql);
    $row=$result_credit_total->fetch_assoc();
    $student_credit_total=$row["SUM(學分數)"];

    $result_student_name=myquery($connection,"SELECT 姓名,班級編號 FROM 學生 WHERE 學號='$studentID';");
    $row=$result_student_name->fetch_assoc();
    $student_name=$row["姓名"];
    $student_class_id=$row["班級編號"];

    $result_class_name=myquery($connection,"SELECT 班級名稱 FROM 班級 WHERE 班級編號=$student_class_id;");
    $row=$result_class_name->fetch_assoc();
    $class_name=$row["班級名稱"];

    echo "學生:$student_name&emsp;學號:$studentID&emsp;班級:$class_name&emsp;總學分:$student_credit_total"
?>
    </div>
    <!-- (e)學生資訊與登出 -->
    <div style="float:left;width:100%;padding:10px 0px;text-align:center">
        <form method="post" action="">
            <input type="text" hidden="hidden" name="post_id" value="<?php echo rand(1, 999999); ?>">
            <input type="text" hidden="hidden" name="post_form" value="login_out">
            <input type="submit" name="submit" value="login out">
        </form>
    </div>
</div>
    </div><!--課表(end)-->
    <div style="float:left;width:10%;height:200px;"></div>
    <div style="float:left;width:48%"><!--搜尋(start)-->
    <form method="post" action="">
        搜尋課程名稱: <input type="text" name="text">
        <input type="text" hidden="hidden" name="post_id" value="<?php echo rand(1, 999999); ?>">
        <input type="text" hidden="hidden" name="post_form" value="course name search">
        <input type="submit" name="submit" value="search">
    </form>
    <form method="post" action="">
        搜尋課程代號: <input type="text" name="text">
        <input type="text" hidden="hidden" name="post_id" value="<?php echo rand(1, 999999); ?>">
        <input type="text" hidden="hidden" name="post_form" value="course id search">
        <input type="submit" name="submit" value="search">
    </form>
    <form method="post" action="">
        依時間搜尋:
<?php
    $daylist=array("一","二","三","四","五");
    echo "星期 <select name='day'>";
    foreach($daylist as $day){
        if(isset($state_refresh)&&isset($_SESSION["tmp_day"])){
            if($_SESSION["tmp_day"]==$day){
                echo "<option value= $day selected>星期$day</option>";
            }else{
                echo "<option value= $day >星期$day</option>";
            }
        }else{
            echo "<option value= $day >星期$day</option>";
        }
    }
    echo "</select> 節 <select name='time' id='mySelect'>";
    for($i=1;$i<15;$i++){
        if(isset($state_refresh)&&isset($_SESSION["tmp_time"])){
            if($_SESSION["tmp_time"]==$i){
                echo "<option value= $i selected >第".$i."節</option>";
            }else{
                echo "<option value= $i >第".$i."節</option>";
            }
        }else{
            echo "<option value= $i >第".$i."節</option>";
        }
    }
    echo "</select>";
?>
        <input type="text" hidden="hidden" name="post_id" value="<?php echo rand(1, 999999); ?>">
        <input type="text" hidden="hidden" name="post_form" value="course time search">
        <input type="submit" name="submit" value="search">
    </form>
    <div class="c" style="width:100%;height:100px;border: 1px solid black;overflow:auto;box-sizing: border-box;padding:10px">
<?php
    if(!empty($reload_page_state["take_course"])){
        if($reload_page_state["take_course"]=="success"){
            echo "加選成功<br>";
        }else{
            foreach($take_course_error as $value){
                switch($value){
                    case "course_full":
                        echo "課程已滿: $text";
                        break;
                    case "course_conflict":
                        $index=0;
                        unset($course_conflict);
                        while($row=$result_course_conflict->fetch_assoc()){
                            $course_conflict[$row["課程代碼"]][$row["星期"]][$index++]=$row["節"];
                        }
                        $count=0;
                        foreach($course_conflict as $key => $value){
                            $count+=count($course_conflict[$key]);
                        }
                        echo "課程衝突:";
                        if($count>1){
                            echo "<br>";
                        }
                        $count=0;
                        foreach($course_conflict as $key => $value){
                            if($count!=0){
                                echo "<br>";
                            }
                            echo $key." ";
                            foreach($value as $key2 => $value2){
                                echo $key2." ";
                                foreach($value2 as $value3){
                                    echo $value3." ";
                                }
                            }
                            $count++;
                        }
                        break;
                    case "course_same":
                        echo "課程名稱與 ";
                        while($row=$result_course_same->fetch_assoc()){
                            echo $row["課程代碼"]." ";
                        }
                        echo "同名";
                        break;
                    case "credit_over":
                        echo "加選後學分數= ".$take_course_credit_over." 超過30學分";
                        break;
                    default:
                }
                echo '<br>';
            }
        }
    }
    if(!empty($reload_page_state["drop_course"])){
        switch($reload_page_state["drop_course"]){
            case "success":
                echo "退選成功";
                break;
            case "have_problem":
                $rand_num=rand(1, 999999);
                echo '
                    退選為必修課，是否確定退選:
                    <form method="post" action="">
                        <input type="text" hidden="hidden" name="post_form" value="drop_course_compulsory">
                        <input type="text" hidden="hidden" name="text" value='.$drop_course_compulsory.'>
                        <input type="text" hidden="hidden" name="post_id" value='.$rand_num.'>
                        <input type="submit" name="submit" value="Yes">
                        <input type="submit" name="submit" value="No">
                    </form>
                    ';
                break;
            case "failure":
                echo "退選後學分數= $drop_course_credit_under 少於9學分";
                break;
            default:
        }
    }
?>
    </div>
    <div style="width:100%;height:50px;"></div>
<?php
    if(!empty($reload_page_state["search"])||(isset($state_refresh)&&isset($_SESSION["tmp_search_course_sql"])&&empty($reload_page_state["search"]))){
        if(empty($reload_page_state["search"])){
            $result_course=myquery($connection,$_SESSION["tmp_search_course_sql"]);
            $reload_page_state["search"]="display";
        }
        switch($reload_page_state["search"]){
            case "display":
                if($result_course->num_rows>0){
                    echo '
                        <div class="c" style="overflow-y:scroll;max-height:63%;border: 1px solid black;box-sizing: border-box;width:100%">
                                <table style="width:100%">
                                    <tr>
                                        <td class="a d">課程代碼</td>
                                        <td class="d">課程名稱</td>
                                        <td class="a d">學分數</td>
                                        <td class="a d">必選修</td>
                                        <td class="a d">課程時間</td>
                                        <td class="a d">開課單位</td>
                                        <td class="a d">教師名稱</td>
                                        <td class="a d">開課人數</td>
                                        <td class="a d">已收授人數</td>
                                        <td></td>
                                    </tr>
                        ';
                    while($row = $result_course->fetch_assoc()){
                        $teacherID=$row["教師ID"];
                        $courseID=$row["課程代碼"];
                        $result_teacherName=myquery($connection,"SELECT 教師名稱 FROM 教師 WHERE 教師ID = '$teacherID';");
                        $row2=$result_teacherName->fetch_assoc();
                        $teacherName=$row2["教師名稱"];
                        $result_studentCourse=myquery($connection,"SELECT * FROM 學生已選課程 WHERE 學號 = '$studentID' AND 課程代碼 ='$courseID';");
                        $botton_text = $result_studentCourse->num_rows!=0 ? '退選' : '加選';
                        $course_id=$row["課程代碼"];
                        $result_course_time=myquery($connection,"SELECT * FROM 課程時間 WHERE 課程代碼 =$courseID;");
                        unset($course_time);
                        unset($index);
                        while($row3=$result_course_time->fetch_assoc()){
                            $day=$row3["星期"];
                            //var_dump($row3);
                            if(empty($index[$day])){
                                $index[$day]=0;
                            }
                            $course_time[$day][$index[$day]++]=$row3["節"];
                        }
                        $rand_num=rand(1, 999999);
                        echo '
                            <tr>
                                <td class="a d">'.$row["課程代碼"].'</td>
                                <td>'.$row["課程名稱"].'</td>
                                <td class="a d">'.$row["學分數"].'</td>
                                <td class="a d">'.$row["必選修"].'</td>
                                <td class="a">
                            ';
                        $week=array("一","二","三","四","五");
                        $count2=0;
                        foreach($week as $day){
                            if(!empty($course_time[$day])){
                                if($count2!=0){
                                    echo "<br>";
                                }
                                sort($course_time[$day]);
                                echo "$day:";
                                $count=0;
                                foreach($course_time[$day] as $time){
                                    if($count!=0){
                                        echo ",";
                                    }
                                    echo $time;
                                    $count++;
                                }
                                $count2++;
                            }
                        }

                        echo '
                            </td>
                            <td>'.$row["開課單位"].'</td>
                            <td class="a d">'.$teacherName.'</td>
                            <td class="a d">'.$row["開課人數"].'</td>
                            <td class="a d">'.$row["已收授人數"].'</td>
                            <td>
                                <form action="" method="post" style="margin:0px auto;">
                                    <input type="text" hidden="hidden" name="text" value='.$course_id.'>
                                    <input type="text" hidden="hidden" name="post_id" value='.$rand_num.'>
                            ';
                        if($botton_text=="加選"){
                            echo '
                                <input type="text" hidden="hidden" name="post_form" value="take course by search result">
                                <input type="submit" name="take_course" value='.$botton_text.'>
                                ';
                        }else{
                            echo '
                                <input type="text" hidden="hidden" name="post_form" value="drop course by search result">
                                <input type="submit" name="take_course" value='.$botton_text.' style="color:red;">
                                ';
                        }
                        echo '
                                    </form>
                                </td>
                            </tr>
                            ';
                    }
                    echo '</table></div>';
                }else {
                    echo '<font>無資料</font>';
                }
                break;
            case "no text":
                echo '<font>輸入為空</font>';
                break;
            case "is string":
                echo '<font>輸入不為數字</font>';
                break;
            default:
        }
    }
?>
    </div><!--搜尋(end)-->
</div>
</body>