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
	<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

	<script src="./js/highstock.js"></script>

	<script src="./js/modules/exporting.js"></script>
	

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
				$('#container2').html("Data Not Found");
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
				$('#container3').html("Data Not Found");
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
				$('#container4').html("Data Not Found");
			}).always(function() {
				return null;
			});
		}

		function getFeatureAverages(){
			$.ajax({
				url: "data_logic.php",
				type: "POST",
				data:{"c_type":5}
			}).done(function (result) {
				//console.log(result.data);
				if(result.status == "Success"){
					$('#container5').highcharts({
						chart: {
							type: 'column'
						},
						title: {
							text: 'Feature stats'
						},
						xAxis: {
							categories: result.data.category
						},
						yAxis: {
							title: {
								text: 'Value'
							}
						},
						series: [{
							name: "Average",
							data: result.data.average
						},{
							name: "Standard Deviation",
							data: result.data.std
						}]
					});
				}else{
					return null;
				}
			}).fail(function( xhr, status, errorThrown ){
				//return null;
				$('#container5').html("Data Not Found");
			}).always(function() {
				return null;
			});
		}

		function getFeaturesLogCount(){
			$.ajax({
				url: "data_logic.php",
				type: "POST",
				data:{"c_type":6}
			}).done(function (result) {
				//console.log(result.data);
				if(result.status == "Success"){
					$('#container6').highcharts({
						chart: {
							type: 'column'
						},
						title: {
							text: 'Feature stats'
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
							name: "Count",
							data: result.data.count
						}]
					});
				}else{
					return null;
				}
			}).fail(function( xhr, status, errorThrown ){
				//return null;
				$('#container6').html("Data Not Found");
			}).always(function() {
				return null;
			});
		}
		function getFeaturesAveragesTracking(){
			$.ajax({
				url: "data_logic.php",
				type: "POST",
				data:{"c_type":7}
			}).done(function (result) {
				if(result.status == "Success"){
					var seriesOptions = [];
					$.each(result.data,function(i,inner_data){
						seriesOptions[i] = {
							name: inner_data.name,
							data: inner_data.data
						}
					});


					Highcharts.stockChart('container7', {

						rangeSelector: {
							selected: 3
						},

						title: {
							text: "Features' tracking"
						},

						yAxis: {
							plotLines: [{
								value: 0,
								width: 2,
								color: 'silver'
							}]
						},

						plotOptions: {
							series: {
								showInNavigator: true
							}
						},

						tooltip: {
							pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b>',
							valueDecimals: 2,
							split: true
						},

						series: seriesOptions
					});
				}else{
					return null;
				}
			}).fail(function( xhr, status, errorThrown ){
				//return null;
				$('#container7').html("Data Not Found");
			}).always(function() {
				return null;
			});
		}
		function getAppsReputationNormalized(){
			$.ajax({
				url: "data_logic.php",
				type: "POST",
				data:{"c_type":8}
			}).done(function (result) {
				//console.log(result.data);
				if(result.status == "Success"){
					$('#container8').highcharts({
						chart: {
							type: 'column'
						},
						title: {
							text: 'Apps reputation'
						},
						xAxis: {
							categories: result.data.apps
						},
						yAxis: {
							title: {
								text: 'Value'
							}
						},
						series: [{
							name: "Reputation",
							data: result.data.reputation
						}]
					});
				}else{
					return null;
				}
			}).fail(function( xhr, status, errorThrown ){
				//return null;
				$('#container8').html("Data Not Found");
			}).always(function() {
				return null;
			});
		}

		function getAppsLogsCount(){
			$.ajax({
				url: "data_logic.php",
				type: "POST",
				data:{"c_type":9}
			}).done(function (result) {
				//console.log(result.data);
				if(result.status == "Success"){
					$('#container9').highcharts({
						chart: {
							type: 'column'
						},
						title: {
							text: 'Apps Logs Count'
						},
						xAxis: {
							categories: result.data.apps
						},
						yAxis: {
							title: {
								text: 'Count'
							}
						},
						series: [{
							name: "Count",
							data: result.data.logscount
						}]
					});
				}else{
					return null;
				}
			}).fail(function( xhr, status, errorThrown ){
				//return null;
				$('#container9').html("Data Not Found");
			}).always(function() {
				return null;
			});
		}


		function getAppsWithLogsForFilter(element){
			$.ajax({
				url: "data_logic.php",
				type: "POST",
				data:{"c_type":00}
			}).done(function(result){
				if(result.status == "Success"){
					//console.log(result.apps);
					//var apps_array = $.parseJSON(result.apps);
					$(element).empty().append($("<option></option>")
						.attr("value","default")
						.text("Please select an app"));

					$.each(result.apps, function(i, item) {
						$(element).append($("<option></option>")
							.attr("value",item.id)
							.text(item.name));
					});

				}


				}).fail(function( xhr, status, errorThrown ){
					return null;
				}).always(function(){
					return null;
				});
				}

		function getAppsFeaturesTracking(app_id){
			console.log(app_id);
			if(app_id == "default"){
				$('#container10').html("Data Not Found");
			}else{
				$.ajax({
					url: "data_logic.php",
					type: "POST",
					data:{"c_type":10,"filters":app_id}
				}).done(function (result) {
					if(result.status == "Success"){
						var seriesOptions = [];

						$.each(result.data,function(i,inner_data){
							seriesOptions[i] = {
								name: inner_data.name,
								data: inner_data.data
							}
						});

						Highcharts.stockChart('container10', {

							rangeSelector: {
								selected: 3
							},

							title: {
								text: "Logs' count tracking"
							},

							yAxis: {
								plotLines: [{
									value: 0,
									width: 2,
									color: 'silver'
								}]
							},

							plotOptions: {
								series: {
									showInNavigator: true
								}
							},

							tooltip: {
								pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b>',
								valueDecimals: 2,
								split: true
							},

							series: seriesOptions
						});
					}else{
						return null;
					}
				}).fail(function( xhr, status, errorThrown ){

					$('#container10').html("Data Not Found");
				}).always(function() {
					return null;
				});

		}

	}
	$( document ).ready(function() {

		getActiveUsers();
		getUsersOS();
		getUsersKnowledge();
		getUsersReputation();
		getFeatureAverages();
		getFeaturesLogCount();
		getFeaturesAveragesTracking();
		getAppsReputationNormalized();
		getAppsLogsCount();

		getAppsWithLogsForFilter("#track_logs_app");	

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

		<div class="panel panel-default">
			<div class="panel-heading">Features Info</div>
			<div class="panel-body">
				<div class="row" style="margin:1%;">
					<div class="col-sm-6">
						<div class="panel panel-default">
							<div class="panel-heading">Current Values</div>
							<div class="panel-body">
								<div id="container5"></div>
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="panel panel-default">
							<div class="panel-heading">Logs Count</div>
							<div class="panel-body">
								<div id="container6"></div>
							</div>
						</div>
					</div>
				</div>
				<div class="row" style="margin:1%;">
					<div class="col-sm-12">
						<div class="panel panel-default">
							<div class="panel-heading">Track</div>
							<div class="panel-body">
								<div id="container7"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>


		<div class="panel panel-default">
			<div class="panel-heading">Apps Info</div>
			<div class="panel-body">
				<div class="row" style="margin:1%;">
					<div class="col-sm-12">
						<div class="panel panel-default">
							<div class="panel-heading">Reputation</div>
							<div class="panel-body">
								<div id="container8"></div>
							</div>
						</div>
					</div>
				</div>
				<div class="row" style="margin:1%;">
					<div class="col-sm-12">
						<div class="panel panel-default">
							<div class="panel-heading">Logs Count</div>
							<div class="panel-body">
								<div id="container9"></div>
							</div>
						</div>
					</div>
				</div>

				<div class="row" style="margin:1%;">
					<div class="col-sm-12">
						<div class="panel panel-default">
							<div class="panel-heading">Logs Count Tracking</div>
							<div class="panel-body">
								<div>
									<select id="track_logs_app" name="track_logs_app" onchange="getAppsFeaturesTracking(this.value)">
										
									</select>
								</div>
								<div id="container10"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>


	</div>
</body>>
</html>