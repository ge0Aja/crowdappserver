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

   $user_thresholds_up_to_date = 0;
   $user_intervals_up_to_date = 0;


   $query = "select user_id, thresholds_up_to_date, intervals_up_to_date from Users where username ='{$_SERVER['PHP_AUTH_USER']}'";
   $user = mysqli_query($con,$query);

   if($user->num_rows !=null && $user->num_rows >0){
    $user_res = mysqli_fetch_row($user);

    $user_thresholds_up_to_date = (int) $user_res[1];
    $user_intervals_up_to_date = (int) $user_res[2];


    $query_update_status = "update Users set active = 1 where username ='{$_SERVER['PHP_AUTH_USER']}'";
    mysqli_query($con,$query_update_status);
    if($_SERVER['PHP_AUTH_PW'] == 'v3Ry$t0ngP@$$w0rd!'){
      $json = file_get_contents('php://input');
      $result = json_decode($json,true);
      foreach($result as $key => $res){
        try{
          $con->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
          foreach($res as $key_2 => $res_2){
            $query_package = "select app_id from Apps where package_name = '$key_2'";
            $app = mysqli_query($con,$query_package);
            if($app->num_rows == null){
              $query_insert_package = "insert into Apps(package_name) values ('$key_2')";
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
              $query_insert_traffic_stats = "insert into traffic_stats (ua_id,rxBytes,txBytes,txPackets,rxPackets,timestamp) values ($user_app_res[0],'".$res_2['rxBytes']."','".$res_2['txBytes']."','".$res_2['txPackets']."','".$res_2['rxPackets']."','".$res_2['Timestamp']."')";
              //file_put_contents('/var/www/uploads/log_'.date("j.n.Y").'.log', $query_insert_traffic_stats, FILE_APPEND);
              if(!mysqli_query($con,$query_insert_traffic_stats)){
                continue;
              }else{
                $query_insert_counter_log = "insert into user_logs_counter (user_app_id,feature_id) values ($user_app_res[0],5), ($user_app_res[0],6), ($user_app_res[0],7), ($user_app_res[0],8)";
                // file_put_contents('/var/www/uploads/log_'.date("j.n.Y").'.log', $query_insert_counter_log, FILE_APPEND);
                mysqli_query($con,$query_insert_counter_log);
                $con->commit();
              }
            }
            
          }catch (Exception $e){
            $error += 1;
            $error_message .= " Exception";
            error_log($e->getMessage()." InsertTF",0);
            $con->rollback();
          }

        }
      }else{
        $error += 1;
        $error_message .= " credentials:unknown ";
      }
    }else{
      $error += 1;
      $error_message .= " credentials:unknown ";
    }
    if($error > 0){
      mysqli_close($con);
      error_log($error_message." InsertTF",0);
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

      if($user_thresholds_up_to_date == 0 && (int)$update_threshold_val[0] == 1){
        $updt_thresh = 1;
      }
      if($user_intervals_up_to_date == 0 && (int)$update_interval_val[0] == 1){
        $updt_intv = 1;
      }
      
      
      echo json_encode(array('status'=>'success','update_interval'=>$updt_intv,'update_threshold'=>$updt_thresh));
      exit();
    }
  }
  catch(Exception $e){
    mysqli_close($con);
    error_log($e->getMessage()." InsertTF",0);
    echo json_encode(array('status'=>'fail','error'=>"Exception"));
    exit();
  }
}
?>
