<?php

$route = '/api/:api_id/images/';
$app->post($route, function ($api_id)  use ($app){

	$host = $_SERVER['HTTP_HOST'];		
	$api_id = decrypt($api_id,$host);

	$ReturnObject = array();
		
 	$request = $app->request(); 
 	$param = $request->params();	
	
	if(isset($param['type']) && isset($param['path']) && isset($param['name']))
		{
			
		$type = trim(mysql_real_escape_string($param['type']));
		$path = trim(mysql_real_escape_string($param['path']));
		$name = trim(mysql_real_escape_string($param['name']));

		$query = "INSERT INTO api_image(API_ID,Image_Name,Image_Path,Type) VALUES(" . $api_id . ",'" . $name . "','" . $path . "','" . $type . "')";
		mysql_query($query) or die('Query failed: ' . mysql_error());					
		$image_id = mysql_insert_id();		
			
		$image_id = encrypt($image_id,$host);	
			
		$F = array();
		$F['image_id'] = $image_id;
		$F['type'] = $type;
		$F['path'] = $path;
		$F['name'] = $name;
		
		array_push($ReturnObject, $F);

		}		

		$app->response()->header("Content-Type", "application/json");
		echo stripslashes(format_json(json_encode($ReturnObject)));
	});	
	
?>