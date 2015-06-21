<?php
$route = '/api/:api_id/tags/';
$app->get($route, function ($api_id)  use ($app){

	$host = $_SERVER['HTTP_HOST'];		
	$api_id = decrypt($api_id,$host);	

	$ReturnObject = array();
		
	$Query = "SELECT t.Tag_ID, t.Tag, count(*) AS API_Count from tags t";
	$Query .= " JOIN api_tag_pivot ctp ON t.Tag_ID = ctp.Tag_ID";
	$Query .= " WHERE ctp.API_ID = " . $api_id;
	$Query .= " GROUP BY t.Tag ORDER BY count(*) DESC";

	$DatabaseResult = mysql_query($Query) or die('Query failed: ' . mysql_error());
	  
	while ($Database = mysql_fetch_assoc($DatabaseResult))
		{

		$tag_id = $Database['Tag_ID'];
		$tag = $Database['Tag'];
		$api_count = $Database['API_Count'];

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