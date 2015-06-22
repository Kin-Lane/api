<?php
$route = '/api/:api_id/images/:image_id';
$app->put($route, function ($api_id,$image_id)  use ($app){

	$host = $_SERVER['HTTP_HOST'];		
	$api_id = prepareIdIn($api_id,$host);
	$image_id = prepareIdIn($image_id,$host);

	$ReturnObject = array();
		
 	$request = $app->request(); 
 	$param = $request->params();	
	
	if(isset($param['type']) && isset($param['path']) && isset($param['name']))
		{
			
		$type = trim(mysql_real_escape_string($param['type']));
		$path = trim(mysql_real_escape_string($param['path']));
		$name = trim(mysql_real_escape_string($param['name']));

		$query = "UPDATE api_image SET Type = '" . $type . "', Image_Name = '" . $name . "', Image_Path = '" . $path . "' WHERE API_Image_ID = " . $image_id;
		mysql_query($query) or die('Query failed: ' . mysql_error());					
		$image_id = mysql_insert_id();		
			
		$image_id = prepareIdOut($image_id,$host);
			
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