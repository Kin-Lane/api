<?php
$route = '/api/:api_id/notes/';
$app->post($route, function ($api_id)  use ($app){

	$host = $_SERVER['HTTP_HOST'];		
	$api_id = prepareIdIn($api_id,$host);

	$ReturnObject = array();
		
 	$request = $app->request(); 
 	$param = $request->params();	
	
	if(isset($param['Type']) && isset($param['Note']))
		{
		$Type = trim(mysql_real_escape_string($param['Type']));
		$Note = trim(mysql_real_escape_string($param['Note']));

		$query = "INSERT INTO api_notes(API_ID,Type,Note) VALUES(" . $api_id . "," . $Type . "," . $Note . "); ";
		mysql_query($query) or die('Query failed: ' . mysql_error());					
		$note_id = mysql_insert_id();		
			
		$note_id = prepareIdOut($note_id,$host);	
			
		$F = array();
		$F['note_id'] = $note_id;
		$F['type'] = $Type;
		$F['note'] = $Note;
		
		array_push($ReturnObject, $F);

		}		

		$app->response()->header("Content-Type", "application/json");
		echo stripslashes(format_json(json_encode($ReturnObject)));
	});	
?>