<?php
$route = '/api/tags/';
$app->get($route, function ()  use ($app){

	$ReturnObject = array();
	
 	$request = $app->request(); 
 	$params = $request->params();	

	$Query = "SELECT t.Tag_ID, t.Tag, count(*) AS API_Count from tags t";
	$Query .= " INNER JOIN api_tag_pivot ctp ON t.Tag_ID = ctp.Tag_ID";
	$Query .= " GROUP BY t.Tag ORDER BY count(*) DESC";

	$DatabaseResult = mysql_query($Query) or die('Query failed: ' . mysql_error());
	  
	while ($Database = mysql_fetch_assoc($DatabaseResult))
		{

		$tag_id = $Database['Tag_ID'];
		
		$host = $_SERVER['HTTP_HOST'];		
		$tag_id =  encrypt($tag_id,$host);		
		
		$tag = $Database['Tag'];
		$api_count = $Database['API_Count'];

		$host = $_SERVER['HTTP_HOST'];		
		$tag_id = prepareIdOut($tag_id,$host);

		$F = array();
		$F['tag_id'] = $tag_id;
		$F['tag'] = $tag;
		$F['api_count'] = $api_count;
		
		array_push($ReturnObject, $F);
		}

		$app->response()->header("Content-Type", "application/json");
		echo stripslashes(format_json(json_encode($ReturnObject)));
	});	
?>