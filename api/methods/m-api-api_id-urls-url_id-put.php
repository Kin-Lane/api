<?php
$route = '/api/:api_id/urls/:url_id';
$app->put($route, function ($api_id,$url_id)  use ($app){

	$host = $_SERVER['HTTP_HOST'];		
	$api_id = decrypt($api_id,$host);
	$url_id = decrypt($url_id,$host);

	$ReturnObject = array();
		
 	$request = $app->request(); 
 	$param = $request->params();	
	
	if(isset($param['type']) && isset($param['url']) && isset($param['name']))
		{
			
		$type = trim(mysql_real_escape_string($param['type']));
		$url = trim(mysql_real_escape_string($param['url']));
		$name = trim(mysql_real_escape_string($param['name']));

		$query = "UPDATE api_urls SET Type = '" . $type . "', URL = '" . $url . "', Name = '" . $name . "' WHERE ID = " . $url_id;
		mysql_query($query) or die('Query failed: ' . mysql_error());					
		$url_id = mysql_insert_id();		
			
		$url_id = encrypt($url_id,$host);	
			
		$F = array();
		$F['url_id'] = $url_id;
		$F['type'] = $type;
		$F['url'] = $url;
		$F['name'] = $name;
		
		array_push($ReturnObject, $F);

		}		

		$app->response()->header("Content-Type", "application/json");
		echo stripslashes(format_json(json_encode($ReturnObject)));
	});	
?>