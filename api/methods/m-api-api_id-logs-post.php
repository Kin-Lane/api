<?php
$route = '/api/:api_id/logs/';
$app->post($route, function ($api_id)  use ($app){

	$host = $_SERVER['HTTP_HOST'];		
	$api_id = decrypt($api_id,$host);

	$ReturnObject = array();
		
 	$request = $app->request(); 
 	$param = $request->params();	
	
	if(isset($param['type']) && isset($param['details']))
		{
		$type = trim(mysql_real_escape_string($param['type']));
		$details = trim(mysql_real_escape_string($param['details']));
		$log_date = date('Y-m-d H:i:s');
		
		$query = "INSERT INTO api_log(API_ID,Type,About,Log_Date) VALUES(" . $api_id . ",'" . $type . "','" . $details . "','" . $log_date . "')";
		mysql_query($query) or die('Query failed: ' . mysql_error());					
		$log_id = mysql_insert_id();		

		$log_id = encrypt($log_id,$host);

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