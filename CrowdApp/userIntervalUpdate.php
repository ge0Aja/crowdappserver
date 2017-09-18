<?php

require "init.php";
$error = 0;
$error_message = "";
if (!isset($_SERVER['PHP_AUTH_USER'])) {
  header('WWW-Authenticate: Basic realm="My Realm"');
  header('HTTP/1.0 401 Unauthorized');
  echo json_encode(array('status'=>'Unauthorized'));
  exit;
}else{
  //echo $_SERVER['PHP_AUTH_USER'];
  try{
    $query = "select user_id from Users where username ='{$_SERVER['PHP_AUTH_USER']}'";
    $user = mysqli_query($con,$query);
    if( $user->num_rows !=null && $user->num_rows >0){
      $user_res = mysqli_fetch_row($user);
      $query_update_status = "update Users set active = 1 where username ='{$_SERVER['PHP_AUTH_USER']}'";
      mysqli_query($con,$query_update_status);
      if($_SERVER['PHP_AUTH_PW'] == 'v3Ry$t0ngP@$$w0rd!'){
        $json = file_get_contents('php://input');
        $obj = json_decode($json);
        $time = $obj->{'timestamp'};
        $status = $obj->{'status'};
        //echo $time.'  '.$status;
        $sqlquery = "update Users set intervals_up_to_date = $status, intervals_update_time = '$time' where
        user_id = $user_res[0]";

        $update_status = mysqli_query($con,$sqlquery);
        if($update_status){
          mysqli_close($con);
          echo json_encode(array('status'=>'success'));
          exit();
        }else{
          error_log(mysqli_error($con),0);
		  mysqli_close($con);
          echo json_encode(array('status'=>'fail'));
          exit();
        }
      }
    }
    mysqli_close($con);
    echo json_encode(array('status'=>'fail','error'=>'credentials:unknown'));
    exit();
  }catch(Exception $e){
    mysqli_close($con);
    error_log($e->getMessage()." userIntervalUpdate",0);
    echo json_encode(array('status'=>'fail','error'=>"Exception"));
    exit();
  }
}

?>
