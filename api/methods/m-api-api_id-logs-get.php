<?php
$route = '/api/:api_id/logs/';
$app->get($route, function ($api_id)  use ($app){

	$host = $_SERVER['HTTP_HOST'];		
	$api_id = prepareIdIn($api_id,$host);

	$ReturnObject = array();
		
	$Query = "SELECT * FROM api_log al";
	$Query .= " WHERE al.API_ID = " . $api_id;

	$DatabaseResult = mysql_query($Query) or die('Query failed: ' . mysql_error());
	  
	while ($Database = mysql_fetch_assoc($DatabaseResult))
		{
			
		$log_id = $Database['API_Log_ID'];
		$details = $Database['About'];
		$pattern = '/[^\w ]+/';
		$replacement = '';
		$about = preg_replace($pattern, $replacement, $about);		
		
		$Type = $Database['Type'];
		$log_date = $Database['Log_Date'];
	
		$image_id = prepareIdOut($image_id,$host);

		$F = array();
		$F['log_id'] = $log_id;
		$F['type'] = $Type;
		$F['details'] = $details;
		$F['log_date'] = $log_date;
		
		array_push($ReturnObject, $F);
		}

		$app->response()->header("Content-Type", "application/json");
		echo stripslashes(format_json(json_encode($ReturnObject)));
	});	
?>