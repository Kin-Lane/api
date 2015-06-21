<?php
$route = '/api/:api_id/buildingblocks/';
$app->post($route, function ($api_id)  use ($app){

	$host = $_SERVER['HTTP_HOST'];		
	$api_id = decrypt($api_id,$host);

	$ReturnObject = array();
		
 	$request = $app->request(); 
 	$param = $request->params();	
	
	if(isset($param['api_id']) && isset($param['building_block_id']))
		{
			
		$building_block_id = trim(mysql_real_escape_string($param['building_block_id']));
		$organization_id = trim(mysql_real_escape_string($param['organization_id']));
		if($organization_id==''){ $organization_id = 0; }
		$tools_id = trim(mysql_real_escape_string($param['tools_id']));
		if($tools_id==''){ $tools_id = 0; }
		$url = trim(mysql_real_escape_string($param['url']));

		$building_block_id = decrypt($building_block_id,$host);
		$organization_id = decrypt($organization_id,$host);
		$tools_id = decrypt($tools_id,$host);

		$query = "INSERT INTO api_building_block_pivot(API_ID,Building_Block_ID,Company_ID,Tools_ID,URL) VALUES(" . $api_id . "," . $building_block_id . "," . $organization_id . "," . $tools_id . ",'" . $url . "'); ";
		mysql_query($query) or die('Query failed: ' . mysql_error());					
		$buildingblock_id = mysql_insert_id();		
			
		$building_block_id = encrypt($building_block_id,$host);
		$organization_id = encrypt($organization_id,$host);
		$tools_id = encrypt($tools_id,$host);			
			
		$F = array();
		$F['building_block_id'] = $building_block_id;
		$F['organization_id'] = $organization_id;
		$F['tools_id'] = $tools_id;
		$F['url'] = $url;
		
		array_push($ReturnObject, $F);

		}		

		$app->response()->header("Content-Type", "application/json");
		echo stripslashes(format_json(json_encode($ReturnObject)));
	});	
?>