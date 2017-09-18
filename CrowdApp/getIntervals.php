<?php
require "init.php";

if (!isset($_SERVER['PHP_AUTH_USER'])) {
  header('WWW-Authenticate: Basic realm="My Realm"');
  header('HTTP/1.0 401 Unauthorized');
  echo 'Unauthorized';
  exit;
}else{
  $query = "select user_id from Users where username ='{$_SERVER['PHP_AUTH_USER']}'";
  $user = mysqli_query($con,$query);
  if($user->num_rows !=null && $user->num_rows >0){
    $query_update_status = "update Users set active = 1 where username ='{$_SERVER['PHP_AUTH_USER']}'";
    mysqli_query($con,$query_update_status);
    if($_SERVER['PHP_AUTH_PW'] == 'v3Ry$t0ngP@$$w0rd!'){
      $query = "select t.type, i.value from  intervals i join intervals_types t on i.interval_type = t.id";
      $results = mysqli_query($con,$query);
      $intervals_array = array();
      if($results->num_rows !=null && $results->num_rows >0){

        while($row = $results->fetch_array(MYSQLI_ASSOC)){
          //$thrsh_array[$row['type']] = $row['value'];
          $intervals_array[$row['type']] = $row['value'];
        }
        //	print_r($intervals_array);
        //exit();
        echo json_encode(array('status'=>'success','intervals'=>$intervals_array));
        mysqli_close($con);
        exit();

      }else{
        $error_message = "Intervals are not set";
        echo json_encode(array('status'=>'finished','error_message'=>$error_message));
        mysqli_close($con);
        exit();
      }
    }
  }
  $error_message = " credentials:unknown ";
  echo json_encode(array('status'=>'finished','error_message'=>$error_message));
  error_log("Wrong Username or Password at authentication getIntervals",0);
  mysqli_close($con);
  exit();
}

?>
