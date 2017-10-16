<?php

header('Content-Type: application/json');

require '../init_t.php';


if ($_SERVER['SERVER_ADDR'] != $_SERVER['REMOTE_ADDR']){
	$this->output->set_status_header(400, 'No Remote Access Allowed');
  exit; //just for good measure
}

try{


	if(!isset($_POST["c_type"])){
		echo json_encode(array('status' => 'Error'));
		mysqli_close($con);
		exit();
	}

	$c_type = $_POST["c_type"];

	if(isset($_POST["filters"])){
		$filters = json_decode($_POST["filters"]);	
	}


	$sql='';
	$view_res = null;
	switch ($c_type) {
		case 00:
		$sql = "select distinct a.app_id,a.app_name from user_logs_counter ulc join user_apps ua on ua.ua_id = ulc.user_app_id join Apps a on a.app_id = ua.app_id";
		$apps_with_logs = mysqli_query($con,$sql);
		if($apps_with_logs && $apps_with_logs->num_rows > 0){
			$apps = array();
			while($apps_with_logs_res = mysqli_fetch_row($apps_with_logs)){
				array_push($apps, array('id' => $apps_with_logs_res[0],'name' => $apps_with_logs_res[1]));
			}

			echo json_encode(array('status' => 'Success', 'apps'=> $apps));
			mysqli_close($con);
			exit();
		}
		break;
		case 1:
		$sql = "SELECT active,count(*) as count FROM `Users` GROUP by active";
		$view = mysqli_query($con,$sql);
		if($view && $view->num_rows > 0){
			$view_res = mysqli_fetch_all($view);
			$integerIDs = array_map('intval',$view_res[0]);
			echo json_encode(array('status' => 'Success', 'data'=> $integerIDs));
			mysqli_close($con);
			exit();
		}
		break;

		case 2:
		$sql = "SELECT os_version,count(*) FROM `Users` GROUP by os_version";
		$view = mysqli_query($con,$sql);
		$category = array();
		$count = array();
		if($view && $view->num_rows > 0){
			while($view_res = mysqli_fetch_row($view)){
				array_push($category, $view_res[0]);
				array_push($count, $view_res[1]+0);
			}
			echo json_encode(array('status' => 'Success', 'data'=> array('category' => $category, 'count' => $count)));
			mysqli_close($con);
			exit();
		}
		break;

		case 3:
		$sql = "SELECT it_knowledge,count(*) FROM `Users` GROUP by it_knowledge";
		$view = mysqli_query($con,$sql);
		$category = array();
		$count = array();
		if($view && $view->num_rows > 0){
			while($view_res = mysqli_fetch_row($view)){
				array_push($category, $view_res[0]);
				array_push($count, $view_res[1]+0);
			}
			echo json_encode(array('status' => 'Success', 'data'=> array('category' => $category, 'count' => $count)));
			mysqli_close($con);
			exit();
		}
		break;

		case 4:
		$sql = "select t.reputation as `range`, count(*) as `users_count`  
		from (  
		select case    
		when `user_reputation` >= 0 and `user_reputation` < 0.2 then ' 0-0.2'  
		when `user_reputation` >= 0.2 and `user_reputation` < 0.4 then ' 0.2-0.4'  
		when `user_reputation` >= 0.4 and `user_reputation` < 0.6 then ' 0.4-0.6'  
		when `user_reputation` >= 0.6 and `user_reputation` < 0.8 then ' 0.6-0.8'  
		when `user_reputation` >= 0.8 and `user_reputation` <= 1 then ' 0.8-1'  
		end as reputation  
		from Users) t  
		group by t.reputation";
		$view = mysqli_query($con,$sql);
		$category = array();
		$count = array();
		if($view && $view->num_rows > 0){
			while($view_res = mysqli_fetch_row($view)){
				array_push($category, $view_res[0]);
				array_push($count, $view_res[1]+0);
			}
			echo json_encode(array('status' => 'Success', 'data'=> array('category' => $category, 'count' => $count)));
			mysqli_close($con);
			exit();
		}
		break;

		case 5:
		$sql = "SELECT `feature_name`,`average_value`,`std_value` FROM `features`";
		$view = mysqli_query($con,$sql);
		$category = array();
		$average = array();
		$std = array();
		if($view && $view->num_rows > 0){
			while($view_res = mysqli_fetch_row($view)){
				if($view_res[0] == "prRSS" || $view_res[0] == "prVSS" || $view_res[0] == "txBytes" || $view_res[0] == "rxBytes" ){
					array_push($category, $view_res[0]."*10^-3");
					array_push($average, ($view_res[1]+0.0)/1000);
					array_push($std, ($view_res[2]+0.0)/1000);
				}else{
					array_push($category, $view_res[0]);
					array_push($average, $view_res[1]+0.0);
					array_push($std, $view_res[2]+0.0);
				}

			}
			echo json_encode(array('status' => 'Success', 'data'=> array('category' => $category, 'average' => $average, 'std' => $std)));
			mysqli_close($con);
			exit();
		}
		break;

		case 6:
		$sql = "SELECT ft.feature_name,count(*) FROM `user_logs_counter` ul join user_apps ua on ul.user_app_id = ua.ua_id join features ft on ft.ft_id = ul.feature_id group by ft.feature_name";
		$view = mysqli_query($con,$sql);
		$category = array();
		$count = array();
		if($view && $view->num_rows > 0){
			while($view_res = mysqli_fetch_row($view)){

				array_push($category, $view_res[0]);
				array_push($count, $view_res[1]+0.0);


			}
			echo json_encode(array('status' => 'Success', 'data'=> array('category' => $category, 'count' => $count)));
			mysqli_close($con);
			exit();
		}
		break;

		case 7:

		$sql = "SELECT DISTINCT ft_id,feature_name from features";
		$features = mysqli_query($con,$sql);
		$features_series = array();
		if($features && $features->num_rows > 0){
			while($features_res = mysqli_fetch_row($features)){
				$sql_i = "SELECT CONCAT(UNIX_TIMESTAMP(time),'000'), cast(case when feature_id in(3,4,5,6) then feature_avg/1000 when feature_id in (1,2,7,8,11) then feature_avg end as decimal(20,2))  from features_tracking WHERE feature_id = $features_res[0]";
				$feature_vals = mysqli_query($con,$sql_i);
				if($feature_vals && $feature_vals->num_rows > 0){
					if($features_res[1] == "prRSS" || $features_res[1] == "prVSS" || $features_res[1] == "txBytes" || $features_res[1] == "rxBytes" ){
						$app_name = $features_res[1].'*10^-3';
					}else{
						$app_name = $features_res[1];
					}
					
					$app_series = array();
					while($feaures_vals_res = mysqli_fetch_row($feature_vals)){
						array_push($app_series, array($feaures_vals_res[0]+0,$feaures_vals_res[1]+0));
					}
					array_push($features_series, array("name" => $app_name, "data" => $app_series));
				} 

			}
			echo json_encode(array('status' => 'Success', 'data'=> $features_series));
			mysqli_close($con);
			exit();
		}
		break;

		case 8:
		$sql = "select case when app_name is null then package_name else app_name end as app, app_reputation_normalized as reputation from Apps";
		$apps = mysqli_query($con,$sql);
		$appsArray = array();
		$reputation = array();
		if($apps && $apps->num_rows > 0){
			while($apps_res = mysqli_fetch_row($apps)){
				array_push($appsArray, $apps_res[0]);
				array_push($reputation, $apps_res[1]+0.0);
			}
			echo json_encode(array('status' => 'Success', 'data'=> array('apps' => $appsArray, 'reputation' => $reputation)));
			mysqli_close($con);
			exit();
		}
		break;


		case 9:

		$sql = "SELECT case when a.app_name is null then a.package_name else a.app_name end as name ,count(*) FROM `user_logs_counter` ul join user_apps ua on ul.user_app_id = ua.ua_id join Apps a on a.app_id = ua.app_id group by name";
		$apps = mysqli_query($con,$sql);
		$appsArray = array();
		$logsCount = array();
		if($apps && $apps->num_rows > 0){
			while($apps_res = mysqli_fetch_row($apps)){
				array_push($appsArray, $apps_res[0]);
				array_push($logsCount, $apps_res[1]+0.0);
			}
			echo json_encode(array('status' => 'Success', 'data'=> array('apps' => $appsArray, 'logscount' => $logsCount)));
			mysqli_close($con);
			exit();
		}

		break;

		case 10:
		$sql = "SELECT DISTINCT ft_id,feature_name from features";
		$features = mysqli_query($con,$sql);
		$features_series = array();
		if($features && $features->num_rows > 0){
			while($features_res = mysqli_fetch_row($features)){
				$sql_i = "SELECT feature_id,concat(UNIX_TIMESTAMP(`timestamp`),'000') as time,count(*) FROM `user_logs_counter` ul join user_apps ua on ul.user_app_id = ua.ua_id join Apps a on a.app_id = ua.app_id where a.app_id = $filters and feature_id = $features_res[0] group by time";
				$feature_vals = mysqli_query($con,$sql_i);
				if($feature_vals && $feature_vals->num_rows > 0){
					$feature_series = array();
					while ($feature_vals_res = mysqli_fetch_row($feature_vals)) {
						array_push($feature_series, array($feature_vals_res[1]+0,$feature_vals_res[2]+0));
					}
					array_push($features_series, array("name" => $features_res[1], "data" => $feature_series));
				}
			}
			echo json_encode(array('status' => 'Success', 'data'=> $features_series));
			mysqli_close($con);
			exit();
		}
		break;

		default:
			echo json_encode(array('status' => 'Error', 'message'=> "No such chart"));
			mysqli_close($con);
			exit();
		break;

	}

	echo json_encode(array('status' => 'NoData'));
	mysqli_close($con);
	exit();

}catch(Exception $e){
	echo json_encode(array('status' => 'Error','message' => $e->getMessage()));
	mysqli_close($con);
	exit();
}



?>