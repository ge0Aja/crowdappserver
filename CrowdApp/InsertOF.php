<?php
require "init.php";
$error = 0;
$error_message = " ";
if (!isset($_SERVER['PHP_AUTH_USER'])) {
  header('WWW-Authenticate: Basic realm="My Realm"');
  header('HTTP/1.0 401 Unauthorized');
  echo json_encode(array('status'=>'Unauthorized'));
  exit;
} else {
  try{
    $query = "select user_id, thresholds_up_to_date, intervals_up_to_date from Users where username ='{$_SERVER['PHP_AUTH_USER']}'";
    $user = mysqli_query($con,$query);
    if($user->num_rows !=null && $user->num_rows >0){
      $user_res = mysqli_fetch_row($user);
      $query_update_status = "update Users set active = 1 where username ='{$_SERVER['PHP_AUTH_USER']}'";
      mysqli_query($con,$query_update_status);
      if($_SERVER['PHP_AUTH_PW'] == 'v3Ry$t0ngP@$$w0rd!'){
        $json = file_get_contents('php://input');
        $result = json_decode($json,true);
        foreach($result as $key => $res){
          try{
            $con->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
            $query_package = "select app_id from Apps where package_name = '".$res['appName']."'";
            $app = mysqli_query($con,$query_package);
            if($app->num_rows == null){
              $query_insert_package = "insert into Apps(package_name) values ('".$res['appName']."')";
              if(mysqli_query($con,$query_insert_package)){
                $app = mysqli_query($con,$query_package);
              }
            }
            $app_res = mysqli_fetch_row($app);
            $query_user_app = "select ua_id from user_apps where app_id = $app_res[0] and user_id = $user_res[0]"; //[0]
            $user_app = mysqli_query($con,$query_user_app);

            if($user_app->num_rows == null){
              $query_insert_user_app = "insert into user_apps (user_id,app_id) values ($user_res[0],$app_res[0])"; //[0]
              if(mysqli_query($con,$query_insert_user_app)){
                $user_app = mysqli_query($con,$query_user_app);
              }
            }
            $user_app_res = mysqli_fetch_row($user_app);
            $query_insert_OF_stats = "insert into app_usage (ua_id,interval_id,type,timestamp) values ($user_app_res[0],(select id from intervals where value =".$res['interval']."),'".$res['type']."','".$res['timestamp']."')"; //
            if(!mysqli_query($con,$query_insert_OF_stats)){
              $error += 1;
              $con->rollback();
            }else{
              $con->commit();
            }
          }catch(Exception $e){
            $error += 1;
            $error_message .= " Exception";
            error_log($e->getMessage()." InsertOF",0);
            $con->rollback();
          }
        }
      }else{
        $error += 1;
        $error_message .= " credentials:unknown ";
      }
    }
    else{
      $error += 1;
      $error_message .= " credentials:unknown ";
    }
    if($error > 0){
      mysqli_close($con);
        error_log($error_message." InsertOF",0);
      echo json_encode(array('status'=>'finished','errors'=>$error,'error_message'=>$error_message));
      exit();
    }else{
      $queryIntervalUpdate = "select value from control where id =1";
      $update_interval = mysqli_query($con,$queryIntervalUpdate);
      if($update_interval->num_rows !=null && $update_interval->num_rows > 0){
        $update_interval_val = mysqli_fetch_row($update_interval);
      }
      $queryThresholdUpdate = "select value from control where id =2";
      $update_threshold = mysqli_query($con,$queryThresholdUpdate);
      if($update_threshold->num_rows !=null && $update_threshold->num_rows > 0){
        $update_threshold_val = mysqli_fetch_row($update_threshold);
      }

      mysqli_close($con);

      $updt_thresh = 0;
      $updt_intv = 0;
      if((int)$user_res[1] == 1 && (int)$update_threshold_val[0] == 1){
        $updt_thresh = 1;
      }
      if((int)$user_res[2] == 1 && (int)$update_interval_val[0] == 1){
        $updt_intv = 1;
      }
      echo json_encode(array('status'=>'success','update_interval'=>$updt_intv,'update_threshold'=>$updt_thresh));
      exit();
      
    }
  }
  catch(Exception $e){
    mysqli_close($con);
    error_log($e->getMessage()." InsertOF",0);
    echo json_encode(array('status'=>'fail','error'=>"Exception"));
    exit();
  }
}
?>
