<?php
$route = '/api/:api_id/logs/:log_id';
$app->delete($route, function ($api_id,$log_id)  use ($app){

	$host = $_SERVER['HTTP_HOST'];		
	$api_id = prepareIdIn($api_id,$host);
	$log_id = prepareIdIn($log_id,$host);

	$ReturnObject = array();
		
 	$request = $app->request(); 
 	$param = $request->params();	

	$DeleteQuery = "DELETE FROM api_log WHERE API_Log_ID = " . $log_id;
	$DeleteResult = mysql_query($DeleteQuery) or die('Query failed: ' . mysql_error());

	$log_id = prepareIdOut($log_id,$host);
	
	$F = array();
	$F['log_id'] = $log_id;
	$F['type'] = '';
	$F['details'] = '';
	$F['log_date'] = '';
	
	array_push($ReturnObject, $F);	

	$app->response()->header("Content-Type", "application/json");
	echo stripslashes(format_json(json_encode($ReturnObject)));
	
	});	
?>