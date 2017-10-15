<?php
require "init.php";

if (!isset($_SERVER['PHP_AUTH_USER'])) {
  header('WWW-Authenticate: Basic realm="My Realm"');
  header('HTTP/1.0 401 Unauthorized');
  echo 'Unauthorized';
  exit;
}
else{
  $query = "select user_id from Users where username ='{$_SERVER['PHP_AUTH_USER']}'";
  $user = mysqli_query($con,$query);
  if($user->num_rows !=null && $user->num_rows >0){

    $user_res = mysqli_fetch_row($user);
    $query_update_status = "update Users set active = 1 where username ='{$_SERVER['PHP_AUTH_USER']}'";
    mysqli_query($con,$query_update_status);
    if($_SERVER['PHP_AUTH_PW'] == 'v3Ry$t0ngP@$$w0rd!'){

      $query = "select app.package_name, f.feature_name,ft.th_value as mean,ft.std_value as std  from features_th ft Join features f on ft.feature_id = f.ft_id Join Apps app on ft.app_id = app.app_id
      Join user_apps ua on ft.app_id = ua.app_id where ua.user_id = $user_res[0]"; //
      $results = mysqli_query($con,$query);
      $thrsh_array = array();
      if($results->num_rows !=null && $results->num_rows >0){
        foreach ($results as  $res){
          $thrsh_array[$res['package_name']][$res['feature_name']]['mean'] = $res['mean'];
          $thrsh_array[$res['package_name']][$res['feature_name']]['std'] = $res['std'];

        }
        $query2 = "select  f.feature_name,f.average_value as mean, f.std_value as std from features f where average_value > 0 ";
        $results2 = mysqli_query($con,$query2);

        if($results2->num_rows !=null && $results2->num_rows >0){
          foreach ($results2 as  $res2){
            $thrsh_array['All'][$res2['feature_name']]['mean'] = $res2['mean'];
            $thrsh_array['All'][$res2['feature_name']]['std'] = $res2['std'];
          }
        }
        echo json_encode(array('status'=>'success','thresholds'=>$thrsh_array));
        mysqli_close($con);
        exit();
      }else{
        $error_message = "Thresholds are not set";
        echo json_encode(array('status'=>'finished','error_message'=>$error_message));
        mysqli_close($con);
        exit();

      }

    }

  }
  $error_message = "credentials:unknown ";
  echo json_encode(array('status'=>'finished','error_message'=>$error_message));
  error_log("Wrong Username or Password at authentication getThresholds",0);
  mysqli_close($con);
  exit();
}
?>
