<?php
$route = '/api/:api_id/buildingblocks/:buildingblock_id';
$app->delete($route, function ($api_id,$buildingblock_id)  use ($app){

	$host = $_SERVER['HTTP_HOST'];		
	$api_id = prepareIdIn($api_id,$host);
	$buildingblock_id = prepareIdIn($buildingblock_id,$host);

	$ReturnObject = array();
		
 	$request = $app->request(); 
 	$param = $request->params();	

	$DeleteQuery = "DELETE FROM api_building_block_pivot WHERE Building_Block_ID = " . $buildingblock_id . " AND API_ID = " . $api_id;
	$DeleteResult = mysql_query($DeleteQuery) or die('Query failed: ' . mysql_error());
	
	$api_id = prepareIdOut($api_id,$host);
	$buildingblock_id = prepareIdOut($buildingblock_id,$host);

	$F = array();
	$F['api_id'] = $api_id;
	$F['building_block_id'] = $buildingblock_id;
	
	array_push($ReturnObject, $F);	

	$app->response()->header("Content-Type", "application/json");
	echo stripslashes(format_json(json_encode($ReturnObject)));
	
	});	
?>