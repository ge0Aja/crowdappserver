<?php
require "init.php";

if (!isset($_SERVER['PHP_AUTH_USER'])) {
  header('WWW-Authenticate: Basic realm="My Realm"');
  header('HTTP/1.0 401 Unauthorized');
  echo 'Unauthorized';
  exit();
}
else{
  $query = "select user_id from Users where username ='{$_SERVER['PHP_AUTH_USER']}'";
  $user = mysqli_query($con,$query);
  if($user->num_rows !=null && $user->num_rows >0){

    $user_res = mysqli_fetch_row($user);
    $query_update_status = "update Users set active = 1 where username ='{$_SERVER['PHP_AUTH_USER']}'";
    mysqli_query($con,$query_update_status);
    if($_SERVER['PHP_AUTH_PW'] == 'v3Ry$t0ngP@$$w0rd!'){

      $query = "select ifnull(app_name,package_name) as name , app_reputation_normalized from Apps join user_apps ua on Apps.app_id = ua.app_id  where user_id = $user_res[0] and Apps.package_name not like 'com.farah.heavyservice'"; //
      $results = mysqli_query($con,$query);
      $thrsh_array = array();
      if($results->num_rows !=null && $results->num_rows >0){
        foreach ($results as  $res){
          $ratings_array[$res['name']]= $res['app_reputation_normalized'];
        }

        echo json_encode(array('status'=>'success','ratings'=>$ratings_array));
        mysqli_close($con);
        exit();
      }else{
        $error_message = "No App ratings found in DB";
        echo json_encode(array('status'=>'finished','error_message'=>$error_message));
        mysqli_close($con);
        exit();
      }

    }
  }
  $error_message = "credentials:unknown, username:".$_SERVER['PHP_AUTH_USER'];
  error_log("Wrong Username or Password at authentication getAppRatings",0);
  echo json_encode(array('status'=>'finished','error_message'=>$error_message));
  mysqli_close($con);
  exit();
}
?>
