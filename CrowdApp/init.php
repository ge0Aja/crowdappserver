<?php
$host = "localhost";
$user = "crowdappuser";
$pass = "PD1GmA6a@HHunYH3";
$dbname = "crowdappdb";

$con = mysqli_connect($host,$user,$pass,$dbname);

if($con){
 // error_log("DB connection success",0);
}else{
  error_log("DB connection error",0);
  error_log(mysqli_connect_error());
  exit();
}
?>
