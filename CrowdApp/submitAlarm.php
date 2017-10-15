<?php
require "init.php";

$error = 0;
$error_message = " ";

if (!isset($_SERVER['PHP_AUTH_USER'])) {
  header('WWW-Authenticate: Basic realm="My Realm"');
  header('HTTP/1.0 401 Unauthorized');
  echo json_encode(array('status'=>'Unauthorized'));
  exit;
} else{
  try{


   $user_thresholds_up_to_date = 0;
   $user_intervals_up_to_date = 0;


   $query = "select user_id, thresholds_up_to_date, intervals_up_to_date from Users where username ='{$_SERVER['PHP_AUTH_USER']}'";
   $user = mysqli_query($con,$query);
   if( $user->num_rows !=null && $user->num_rows >0){
    $user_res = mysqli_fetch_row($user);

    $user_thresholds_up_to_date = (int) $user_res[1];
    $user_intervals_up_to_date = (int) $user_res[2];


    $query_update_status = "update Users set active = 1 where username ='{$_SERVER['PHP_AUTH_USER']}'";
    mysqli_query($con,$query_update_status);
    if($_SERVER['PHP_AUTH_PW'] == 'v3Ry$t0ngP@$$w0rd!'){

        //begin try
      $json = file_get_contents('php://input');
      $result = json_decode($json);

      $appk  = $result->{'app'};
      $thresh = $result->{'thresh'};
      $val_th = $result->{'value'};
      $prt_th = $result->{'percentage'};
      $time =  $result->{'timestamp'};

      $con->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

      $query_package = "select app_id from Apps where package_name = '".$appk."'";
      $app = mysqli_query($con,$query_package);
      if($app->num_rows == null){
        $query_insert_package = "insert into Apps(package_name) values ('".$appk."')";
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

        $query_last_alarm = "SELECT max(timestamp) from alarms where user_id = $user_res[0] and app_id = $app_res[0] and ft_id = (select ft_id from features where feature_name = '".$thresh."')";

        $last_alarm_exe = mysqli_query($con,$query_last_alarm);
        $last_alarm_res = mysqli_fetch_row($last_alarm_exe);

       // if($last_alarm_exe->num_rows > 0) {
        $diff = ($time+0) - ($last_alarm_res[0]+0);
       // }else{
        //  $diff  = 0;
        //}
        

        $query = "insert into alarms (user_id,app_id,ft_id,val,prt,timestamp,diff) values ($user_res[0],$app_res[0],(select ft_id from features where feature_name = '".$thresh."'),$val_th,$prt_th,'".$time."','".$diff."');";

        $app_id_query = "select app_id from Apps where package_name = '".$appk."'" ;
        $feature_id_query = "select ft_id from features where feature_name = '".$thresh."'";

        $app_id = mysqli_query($con,$app_id_query);
        $feature_id = mysqli_query($con,$feature_id_query);

        $app_id_res = mysqli_fetch_row($app_id);
        $feature_id_res = mysqli_fetch_row($feature_id);


        if(mysqli_query($con,$query)){
          $con->commit();

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
          
         // $mystring = system('python /usr/local/bin/process_trigger_no_proc_salad_georgi.py '.$app_id_res[0].' '.$feature_id_res[0].' '.$time.' '.$val_th.' '.$prt_th);
          exit();
        }else{
          $con->rollback();
          error_log(mysqli_error($con),0);
          echo json_encode(array('status'=>'fail','error'=>'Cannot submit Alarm'));
          mysqli_close($con);
          exit();
        }
      }
    }
    error_log("credentials:unknown submitAlarm",0);
    mysqli_close($con);
    echo json_encode(array('status'=>'fail','error'=>'credentials:unknown'));
    exit();
  }catch(Exception $e){

    mysqli_close($con);
    error_log($e->getMessage()." submitAlarm",0);
    echo json_encode(array('status'=>'fail','error'=>"Exception"));
    exit();

  }
}
?>
