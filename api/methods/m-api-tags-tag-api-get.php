<?php

$route = '/api/tags/:tag/api/';
$app->get($route, function ($tag)  use ($app){

	$ReturnObject = array();
	
 	$request = $app->request(); 
 	$params = $request->params();		

	if(isset($_REQUEST['week'])){ $week = $params['week']; } else { $week = date('W'); }
	if(isset($_REQUEST['year'])){ $year = $params['year']; } else { $year = date('Y'); }	

	$Query = "SELECT c.* from tags t";
	$Query .= " JOIN api_tag_pivot ctp ON t.Tag_ID = ctp.Tag_ID";
	$Query .= " JOIN api c ON ctp.API_ID = c.API_ID";
	$Query .= " WHERE WEEK(c.Item_Date) = " . $week . " AND YEAR(c.Item_Date) = " . $year . " AND Tag = '" . $tag . "'";

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
		$host = $_SERVER['HTTP_HOST'];		
		$api_id =  encrypt($api_id,$host);			
		
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