<?php
$route = '/api/:api_id/';
$app->get($route, function ($api_id)  use ($app){
				
	$host = $_SERVER['HTTP_HOST'];		
	$api_id = prepareIdIn($api_id,$host);	

	$ReturnObject = array();
		
	$Query = "SELECT * FROM api WHERE API_ID = '" . $api_id . "'";
	//echo $Query . "<br />";
	
	$DatabaseResult = mysql_query($Query) or die('Query failed: ' . mysql_error());
	  
	while ($Database = mysql_fetch_assoc($DatabaseResult))
		{
		$api_id = $Database['API_ID'];
		$name = $Database['Name'];
		$about = $Database['About'];
		$pattern = '/[^\w ]+/';
		$replacement = '';
		$about = preg_replace($pattern, $replacement, $about);
				
		$rank = $Database['Kin_Rank'];
		$organization_id = $Database['Company_ID'];		
				
		// manipulation zone
		
		$api_id = prepareIdOut($api_id,$host);		
		
		$F = array();
		$F['api_id'] = $api_id;
		$F['name'] = $name;
		$F['about'] = $about;
		$F['rank'] = $rank;
		$F['organization_id'] = $organization_id;
		
		array_push($ReturnObject, $F);
		}

		$app->response()->header("Content-Type", "application/json");
		echo stripslashes(format_json(json_encode($ReturnObject)));
	});
?>