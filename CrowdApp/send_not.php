<?php
require "init.php";

echo "php logic started";

$_GET = array();

//var_dump($argv);
//exit();

foreach($argv as $key => $pair){
	if($key == 0){
		continue;
	}
	list($key, $value) = explode(":",$pair);
	$_GET[$key] = $value;
}

//print_r($_GET);
$new_row_id = $_GET['row'];
$reported_val = $_GET['th_val'];
$reported_prt = $_GET['th_prt'];

$shared_key = $_GET['key'];

if($shared_key == null || $shared_key != "ThisIsJustAnInternalKeySoItDoesn'tNeedToBeSoLong"){
	error_log("Shared Key is not set will exit send notification",0);
	exit();
}

$sql_getmessage = "select questions.app_id, questions.qt_id, user_id from user_questions join questions on user_questions.q_id = questions.q_id where uq_id =".$new_row_id;
$result = mysqli_query($con,$sql_getmessage);
$row = mysqli_fetch_row($result);
//~ print_r($row);
$sql_getquestion = "select title,question,message,type from questions_types join questions on questions.qt_id = questions_types.qt_id where questions_types.qt_id =".$row[1]." group by questions_types.type";
$sql_getuser = "select fcm_token from Users where user_id =".$row[2];
$sql_getapp = "select app_name,package_name from Apps where app_id =".$row[0];

$result_question = mysqli_query($con,$sql_getquestion);

$row_question = mysqli_fetch_array($result_question);


$result_user = mysqli_query($con,$sql_getuser);
$row_user = mysqli_fetch_row($result_user);

$result_app = mysqli_query($con,$sql_getapp);
$row_app = mysqli_fetch_row($result_app);

if($row_app[0] != ""){
	$title = str_replace("{app}",$row_app[0],$row_question[0]);
}else{
	$title = str_replace("{app}",$row_app[1],$row_question[0]);
}

$question = $row_question[1];


$Qid = $new_row_id;
$alert_type = $row_question[3];
$user = $row_user[0];

if($alert_type == 1 || $alert_type == 2 || $alert_type ==4){
	$reported_val = $reported_val / 1024 ;

}

// added for showing percentage and value
$to_replace_message = array("{app}","{val}","{prt}");
$rep_with_message = array($row_app[0],round($reported_val),round($reported_prt,2));
//~ remove the below comment and add comment to above two lines to return to normal
//~ $message = str_replace("{app}",$row_app[0],$row_question[2]);
$message = str_replace($to_replace_message,$rep_with_message,$row_question[2]);



//~ print_r($title. ' '. $question . '  '. $message. '  '. $Qid. ' '. $alert_type.'  ' .$user  );

$path_for_fcm = 'https://fcm.googleapis.com/fcm/send';
$server_key = "AIzaSyDZLT3gRBjLXTpDuLBrW1aAMhZLd4dvess";

$headers = array(
	'Authorization:key='.$server_key,
	'Content-Type:application/json'
	);

	//$fields = array('to'=>$key,
	//	'notification'=>array('title'=>$title, 'body'=>$message),'data'=>array('type'=>'1','Qid'=>'1','question'=>'How Are you!'));

$fields = array('to'=>$user,
	'data'=>array('master_type' => 'Q','title'=>$title,'body'=>$message,'type'=>$alert_type,'Qid'=>$Qid,'question'=>$question));

$payload = json_encode($fields);

	//print_r($payload."<br/>");

$curl_session = curl_init();

	//print_r($curl_session."<br/>");


curl_setopt($curl_session, CURLOPT_URL, $path_for_fcm);
curl_setopt($curl_session, CURLOPT_POST, true);
curl_setopt($curl_session, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl_session, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl_session, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
curl_setopt($curl_session, CURLOPT_POSTFIELDS, $payload);

$not_res = curl_exec($curl_session);

$json_not_res =  json_decode($not_res,true);

if ($json_not_res['failure'] == 1){
	if(isset($json_not_res['results'][0]['error']) && $json_not_res['results'][0]['error'] == 'NotRegistered'){
		$query_update_user = "update Users set active = 0 where user_id = ".$row[2];
		if(mysqli_query($con,$query_update_user)){
			echo "User is not active any more";
		}

	}
}

curl_close($curl_session);
mysqli_close($con);
exit();

?>
