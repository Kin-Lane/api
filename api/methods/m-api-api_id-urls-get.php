<?php
$route = '/api/:api_id/urls/';
$app->get($route, function ($api_id)  use ($app){
	
	$host = $_SERVER['HTTP_HOST'];		
	$api_id = decrypt($api_id,$host);

	$ReturnObject = array();
		
	$Query = "SELECT * from api_urls cn";
	$Query .= " WHERE cn.API_ID = " . $api_id;

	$DatabaseResult = mysql_query($Query) or die('Query failed: ' . mysql_error());
	  
	while ($Database = mysql_fetch_assoc($DatabaseResult))
		{

		$url_id = $Database['API_URL_ID'];
		$type = $Database['Type'];
		$url = $Database['URL'];
		$name = $Database['Name'];

		$url_id = decrypt($url_id,$host);
		
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