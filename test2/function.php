<?php
    function myquery($connection,$sql){
        if($result=$connection->query($sql)){
            return $result;
        }else{
            echo "執行失敗：$connection->error<br>sql指令:$sql";
            exit();
        }
    }
    function print_query($result){
        if($result->num_rows>0){
            while($row = $result->fetch_assoc()){
                foreach ($row as $key => $value){
                    echo "$key : $value&nbsp;";
                }
                echo "<br>";
            }
            $result->data_seek(0);
        }else{
            echo "no result<br>";
        }
        echo "<br>";
    }
    function myheader($url,$sec){
        header("Refresh:$sec;url=$url");
        exit();
    }
    function dayToNum($day){
        if($day=="一"){
            return 1;
        } elseif ($day=="二"){
            return 2;
        } elseif ($day=="三"){
            return 3;
        } elseif ($day=="四"){
            return 4;
        } else {
            return 5;
        }
    }
?>