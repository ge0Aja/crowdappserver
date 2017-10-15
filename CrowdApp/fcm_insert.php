<?php
require "init.php";
$json = file_get_contents('php://input');
$obj = json_decode($json);
$fcm_token = $obj->{'fcm_token'};
$os_version = $obj->{'os_version'};
$it_know = $obj->{'it_knowledge'};
$time = $obj->{'timestamp'};
$user_apps = json_decode(json_encode($obj->{'apps'}),true);
$pass = $obj->{'pass'};

if($pass == null || $pass != "3o0r71pp"){
	echo json_encode(array("status" => "denied"));
}
$user_name = hash('ripemd160', $fcm_token);
$sqlquery = "select * from Users where username ='$user_name'";
$exist = mysqli_query($con,$sqlquery);
if($exist->num_rows != null && $exist->num_rows > 0){
	$query_update_status = "update Users set active = 1,it_knowledge = '".$it_know."', fcm_token = '".$fcm_token."' where username ='$user_name'";
	mysqli_query($con,$query_update_status);
	echo json_encode(array('status'=>'finished','username'=>$user_name));
	mysqli_close($con);
	exit();
}
try{
	if($fcm_token != "" && $user_name != "" ){
		$con->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
		$sqlquery = "insert into Users(username,fcm_token,os_version,it_knowledge,user_register_time,intervals_update_time,thresholds_update_time) values('".$user_name."','".$fcm_token."','".$os_version."','".$it_know."','".$time."','".$time."','".$time."');";
		if(mysqli_query($con,$sqlquery)){
			$Uquery = "select user_id from Users where username ='$user_name'";
			$user = mysqli_query($con,$Uquery);
			$user_res = mysqli_fetch_row($user);

			foreach($user_apps as $appn){
				$query_package = "select app_id from Apps where package_name = '".$appn['name']."'";
				$app = mysqli_query($con,$query_package);
				if($app->num_rows == null || $app->num_rows == 0){
					$query_insert_package = "insert into Apps(package_name,app_name) values ('".$appn['name']."','".$appn['dispname']."')";
					if(mysqli_query($con,$query_insert_package)){
						$app = mysqli_query($con,$query_package);
					}
				}
				$app_res = mysqli_fetch_row($app);
				$query_user_app = "select ua_id from user_apps where app_id = $app_res[0] and user_id = $user_res[0]"; //[0]
				$user_app = mysqli_query($con,$query_user_app);
				if($user_app->num_rows == null || $user_app->num_rows ==0){
					$query_insert_user_app = "insert into user_apps (user_id,app_id,install_date) values ($user_res[0],$app_res[0],'".$appn['timestamp']."')"; //[0]
					if(mysqli_query($con,$query_insert_user_app)){
						$user_app = mysqli_query($con,$query_user_app);
					}
				}
			}
			$con->commit();
			mysqli_close($con);
			echo json_encode(array('status'=>'success','username'=>$user_name));
			exit();
		}else{
			error_log(mysqli_error($con),0);
			echo json_encode(array("status" => "no_query"));
			exit();
		}
	}else{
		error_log("FCM token or username is null fcm_insert",0);
		echo json_encode(array("status" => "fail"));
		exit();
	}
}catch(Exception $e){
	$con->rollback();
	error_log($e->getMessage(),0);
	echo json_encode(array('status'=>'fail'));
	exit();
}

?>
