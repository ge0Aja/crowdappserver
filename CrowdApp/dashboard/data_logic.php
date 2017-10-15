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

	$chart_type = $_POST["c_type"];

	if(isset($_POST["filters"])){
		$filters = $_POST["filters"];	
	}


	$sql='';
	$view_res = null;
	switch ($chart_type) {
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

		default:
		# code...
		break;
	}

	echo json_encode(array('status' => 'Error t'));
	mysqli_close($con);
	exit();

}catch(Exception $e){
	echo json_encode(array('status' => 'Error c'));
	mysqli_close($con);
	exit();
}



?>