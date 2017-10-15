<!DOCTYPE HTML>
<html>
<head>

<style>
input[type=text], select {
	width: 100%;
	padding: 12px 20px;
	margin: 8px 0;
	display: inline-block;
	border: 1px solid #ccc;
	border-radius: 4px;
	box-sizing: border-box;
}

input[type=submit] {
	width:30%;
	background-color: #4CAF50;
	color: white;
	padding: 14px 20px;
	margin: 8px 0;
	border: none;
	border-radius: 4px;
	cursor: pointer;
}

input[type=submit]:hover {
	background-color: #45a049;
}

div {
	border-radius: 5px;
	background-color: #f2f2f2;
	padding: 20px;
}
</style>

<style>
#randomfield {
	-webkit-touch-callout: none;
	-webkit-user-select: none;
	-khtml-user-select: none;
	-moz-user-select: none;
	-ms-user-select: none;
	user-select: none;
	width: 200px;
	color: black;
	border-color: black;
	text-align: center;
	font-size: 40px;
	opacity: 0.4;
	background-image: url('../images/1.JPG');
}
</style>

<style>
.errortext{
	color: red;
	font-weight: bold;
}
</style>

<script src='https://www.google.com/recaptcha/api.js'></script>

</head>

<?php
require "init.php";
session_start();

$error = array("mob" => "",
"captcha" => "",
"terms" => "");

$error_exist = 0;
$mob_num = "";
$captcha = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {

	if(isset($_POST['CaptchaEnter'])){
		$captcha = $_POST['CaptchaEnter'];
		if($_SESSION['real_captcha'] != $captcha){
			$error["captcha"] = "Please Type the Text shown in the picture";
			$error_exist = 1 ;
		}
	}

	if(isset($_POST["mob_num"])){
		$mob_num = $_POST["mob_num"];
		if(!preg_match('/^\+?\d+$/',$mob_num)){
			$error["mob"] = "Please Type a valid mobile number";
			$error_exist = 1 ;
		}
	}

	if(!$_POST["terms_conditions"] == "agree"){
		$error["terms"] = "Please Accept the Terms and Conditions";
		$error_exist = 1 ;
	}

	if($error_exist == 0){
		try{
			$query_check = "SELECT id from participants where number like '$mob_num'";
			$exists = mysqli_query($con,$query_check);
			$query_check2 = "SELECT id from participants where session_var like '$captcha'";
			$exists2 = mysqli_query($con,$query_check2);
			if( $exists->num_rows > 0){
			$error["mob"] = "Mobile number already exists";
			}
			else if ( $exists2->num_rows >0){
			$error["captcha"] = "Expired";
			}
			else{
			$con->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
			$query = "INSERT INTO participants (`number`,`session_var` ,`timestamp`) values ('$mob_num','$captcha',UNIX_TIMESTAMP())";
			if(!mysqli_query($con,$query)){
				$con->rollback();
				//var_dump( mysqli_error($con));
				error_log(mysqli_error($con),0);
				mysqli_close($con);
			}else{
				$con->commit();
				header("Location: success_par_reg.php");
				mysqli_close($con);
				exit();
			}
			}
			
			
		}catch(Exception $e){
			error_log($e->getMessage(). " participant registration");
		}
	}

	$_POST["CaptchaEnter"] = "";
	$_POST["terms_conditions"] = "";
}

?>

<body >
	<h2>New Participant</h2>
	<div>
		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
			<div>
				<label for="mnum">Mobile Number</label>
				<input type="text" id="mnum" name="mob_num" placeholder="+96171111111" value="<?php echo isset($_POST["mob_num"]) ? $_POST["mob_num"] : ''; ?>" required>
				<label class="errortext"><?php echo $error["mob"]?></label>
			</div>
			<div>

				<image src="captcha_img.php">

					<br>
					<br>
					<label for="CaptchaEnter">Enter the code above here :</label>
					<input id="CaptchaEnter" name="CaptchaEnter" type="text" required>
					<label class="errortext"><?php echo $error["captcha"]?></label>
				</div>

				<div>
					<input type="checkbox" name="terms_conditions" value="agree" required> I Agree with the Terms and Conditions <br>
					<label class="errortext"><?php echo $error["terms"]?></label>
				</div>
				<br>
				<br>
				<div class="g-recaptcha" data-sitekey="6LeyrS8UAAAAAFbGgTLWTBY2o1_nsytJ1HFJMfbA">
				
				</div>
				<br>
				<input type="submit" value="Submit">
			</form>
		</div>

	</body>
	</html>
