<?php
$route = '/api/:api_id/screenshots/';
$app->get($route, function ($api_id)  use ($app){

	$host = $_SERVER['HTTP_HOST'];		
	$api_id = decrypt($api_id,$host);

	$ReturnObject = array();
		
	$Query = "SELECT * FROM api_screenshot as";
	$Query .= " WHERE as.API_ID = " . $api_id;

	$DatabaseResult = mysql_query($Query) or die('Query failed: ' . mysql_error());
	  
	while ($Database = mysql_fetch_assoc($DatabaseResult))
		{
			
		$screenshot_id = $Database['ID'];
		$path = $Database['Image_URL'];
		$name = $Database['Name'];
		$type = $Database['Type'];

		$screenshot_id = encrypt($screenshot_id,$host);
	
		$F = array();
		$F['screenshot_id'] = $screenshot_id;
		$F['name'] = $name;
		$F['path'] = $path;
		$F['type'] = $type;
		
		array_push($ReturnObject, $F);
		}

		$app->response()->header("Content-Type", "application/json");
		echo stripslashes(format_json(json_encode($ReturnObject)));
	});	
?>