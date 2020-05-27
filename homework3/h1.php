<HTML>
<?php
  function hello(){
      echo "hello <br/>";
  }
  hello();
  $arr=array(
      0=>15,
      1=>12,
      2=>13,
  );
  for($i=0;$i<count($arr);$i++){
      echo $arr[$i]; 
      echo "<br/>";
  }
?>
</HTML>