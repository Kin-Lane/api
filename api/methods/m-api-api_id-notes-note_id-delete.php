<?php
$route = '/api/:api_id/notes/:note_id';
$app->delete($route, function ($api_id,$note_id)  use ($app){

	$host = $_SERVER['HTTP_HOST'];		
	$api_id = prepareIdIn($api_id,$host);	
	$note_id = prepareIdIn($note_id,$host);	

	$ReturnObject = array();
		
 	$request = $app->request(); 
 	$param = $request->params();	

	$DeleteQuery = "DELETE FROM api_note WHERE ID = " . trim($note_id) . " AND API_ID = " . trim($api_id);
	$DeleteResult = mysql_query($DeleteQuery) or die('Query failed: ' . mysql_error());

	$note_id = prepareIdOut($note_id,$host);
	
	$F = array();
	$F['note_id'] = $note_id;
	$F['type'] = '';
	$F['note'] = '';
	
	array_push($ReturnObject, $F);	

	$app->response()->header("Content-Type", "application/json");
	echo stripslashes(format_json(json_encode($ReturnObject)));
	
	});	
?>