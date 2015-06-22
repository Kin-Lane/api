<?php
$route = '/api/:api_id/urls/:url_id';
$app->delete($route, function ($api_id,$url_id)  use ($app){

	$host = $_SERVER['HTTP_HOST'];		
	$api_id = prepareIdIn($api_id,$host);
	$url_id = prepareIdIn($url_id,$host);

	$ReturnObject = array();
		
 	$request = $app->request(); 
 	$param = $request->params();	

	$DeleteQuery = "DELETE FROM api_url WHERE ID = " . trim($url_id);
	$DeleteResult = mysql_query($DeleteQuery) or die('Query failed: ' . mysql_error());

	$url_id = prepareIdOut($url_id,$host);

	$F = array();
	$F['url_id'] = $url_id;
	$F['type'] = '';
	$F['url'] = '';
	
	array_push($ReturnObject, $F);	

	$app->response()->header("Content-Type", "application/json");
	echo stripslashes(format_json(json_encode($ReturnObject)));
	
	});	
?>