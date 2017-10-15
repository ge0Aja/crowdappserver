<?php

if (!isset($_SERVER['PHP_AUTH_USER'])) {
	header('WWW-Authenticate: Basic realm="My Realm"');
	header('HTTP/1.0 401 Unauthorized');
	echo json_encode(array('status'=>'Unauthorized'));
	exit;
} else{

	try{
		if($_SERVER['PHP_AUTH_PW'] != 'v3RyW3AkP@$$w0rd!' && $_SERVER['PHP_AUTH_USER'] != "crowdab"){
			return json_encode(array('status' => 'Unauthorized'));
			exit();
		}

		$secret_key = "P1sPhR1s3F0rQuery";

	}catch(Exception $e){

	}
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>CrowdApp Dashboard</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

	<script src="https://code.highcharts.com/stock/highstock.js"></script>
	<script src="https://code.highcharts.com/highcharts.js"></script>
	<script src="https://code.highcharts.com/modules/exporting.js"></script>
	<script src="https://code.highcharts.com/stock/modules/exporting.js"></script>

	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="utf-8">

	<script>
		function getActiveUsers(){
			$.ajax({
				url: "data_logic.php",
				type: "POST",
				data:{"c_type":1}
			}).done(function (result) {
				//console.log(result.data);
				if(result.status == "Success"){
					$('#container1').highcharts({
						chart: {
							type: 'column'
						},
						title: {
							text: 'Active Users'
						},
						xAxis: {
							categories: ['Inactive','Active']
						},
						yAxis: {
							title: {
								text: 'Count'
							}
						},
						series: [{
							name: "Users",
							data: result.data
						}]
					});
				}else{
					return null;
				}
			}).fail(function( xhr, status, errorThrown ){
				//return null;
				$('#container1').html("Data Not Found");
			}).always(function() {
				return null;
			});
		}

		function getUsersOS(){
			$.ajax({
				url: "data_logic.php",
				type: "POST",
				data:{"c_type":2}
			}).done(function (result) {
				//console.log(result.data);
				if(result.status == "Success"){
					$('#container2').highcharts({
						chart: {
							type: 'column'
						},
						title: {
							text: 'API stats'
						},
						xAxis: {
							categories: result.data.category
						},
						yAxis: {
							title: {
								text: 'Count'
							}
						},
						series: [{
							name: "APIs",
							data: result.data.count
						}]
					});
				}else{
					return null;
				}
			}).fail(function( xhr, status, errorThrown ){
				//return null;
				$('#container1').html("Data Not Found");
			}).always(function() {
				return null;
			});
		}

		function getUsersKnowledge(){
			$.ajax({
				url: "data_logic.php",
				type: "POST",
				data:{"c_type":3}
			}).done(function (result) {
				//console.log(result.data);
				if(result.status == "Success"){
					$('#container3').highcharts({
						chart: {
							type: 'column'
						},
						title: {
							text: 'Users\' IT knowledge'
						},
						xAxis: {
							categories: result.data.category
						},
						yAxis: {
							title: {
								text: 'Count'
							}
						},
						series: [{
							name: "IT knowledge",
							data: result.data.count
						}]
					});
				}else{
					return null;
				}
			}).fail(function( xhr, status, errorThrown ){
				//return null;
				$('#container1').html("Data Not Found");
			}).always(function() {
				return null;
			});
		}

		function getUsersReputation(){
			$.ajax({
				url: "data_logic.php",
				type: "POST",
				data:{"c_type":4}
			}).done(function (result) {
				//console.log(result.data);
				if(result.status == "Success"){
					$('#container4').highcharts({
						chart: {
							type: 'column'
						},
						title: {
							text: 'Users\' Reputation Distribution'
						},
						xAxis: {
							categories: result.data.category
						},
						yAxis: {
							title: {
								text: 'Count'
							}
						},
						series: [{
							name: "Reputation Distribution",
							data: result.data.count
						}]
					});
				}else{
					return null;
				}
			}).fail(function( xhr, status, errorThrown ){
				//return null;
				$('#container1').html("Data Not Found");
			}).always(function() {
				return null;
			});
		}
		$( document ).ready(function() {

			getActiveUsers();
			getUsersOS();
			getUsersKnowledge();
			getUsersReputation();

		});
	</script>
</head>
<body>
	<div class="container">
		<div class="panel panel-default">
			<div class="panel-heading">User Info</div>
			<div class="panel-body">
				<div class="row" style="margin:1%;">
					<div class="col-sm-6">
						<div class="panel panel-default">
							<div class="panel-heading">Active Users</div>
							<div class="panel-body">
								<div id="container1"></div>
							</div>
						</div>
						<div class="panel panel-default">
							<div class="panel-heading">API stats</div>
							<div class="panel-body">
								<div id="container2"></div>
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="panel panel-default">
							<div class="panel-heading">IT knowledge</div>
							<div class="panel-body">
								<div id="container3"></div>
							</div>
						</div>
						<div class="panel panel-default">
							<div class="panel-heading">Reputation Distribution</div>
							<div class="panel-body">
								<div id="container4"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>>
</html>