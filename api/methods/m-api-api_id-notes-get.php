<?php
$route = '/api/:api_id/notes/';
$app->get($route, function ($api_id)  use ($app){

	$host = $_SERVER['HTTP_HOST'];		
	$api_id = prepareIdIn($api_id,$host);	

	$ReturnObject = array();
		
	$Query = "SELECT ID,Curate_ID,Type,Note  from api_notes cn";
	$Query .= " WHERE cn.API_ID = " . $api_id;

	$DatabaseResult = mysql_query($Query) or die('Query failed: ' . mysql_error());
	  
	while ($Database = mysql_fetch_assoc($DatabaseResult))
		{

		$note_id = $Database['ID'];
		$Type = $Database['Type'];
		$Note = $Database['Note'];
	
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