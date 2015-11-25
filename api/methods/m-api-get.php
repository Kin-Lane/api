<?php

$route = '/api/';
$app->get($route, function ()  use ($app,$contentType){

	$ReturnObject = array();
	$ReturnObject['contentType'] = $contentType;

	if($contentType = 'application/vnd.apis+json')
		{
		$app->response()->header("Content-Type", "application/json");

		$apis_json_url = "http://" . $githuborg . ".github.io/" . $githubrepo . "/apis.json";
		$apis_json = file_get_contents($apis_json_url);
		echo stripslashes(format_json($apis_json));
		}
	else
		{
		if(isset($_REQUEST['query'])){ $query = $_REQUEST['query']; } else { $query = '';}

		if($query!='')
			{
			$Query = "SELECT * FROM api WHERE Name LIKE '%" . $query . "%' OR About LIKE '%" . $query . "%'";
			}
		else
			{
			$Query = "SELECT * FROM api";
			}

		$Query .= " ORDER BY Name ASC";

		$Query .= " LIMIT 250";
		//echo $Query . "<br />";

		$DatabaseResult = mysql_query($Query) or die('Query failed: ' . mysql_error());

		while ($Database = mysql_fetch_assoc($DatabaseResult))
			{

			$api_id = $Database['API_ID'];

			$host = $_SERVER['HTTP_HOST'];
			$api_id = prepareIdOut($api_id,$host);

			$name = $Database['Name'];
			$about = $Database['About'];
			$pattern = '/[^\w ]+/';
			$replacement = '';
			$about = preg_replace($pattern, $replacement, $about);

			$rank = $Database['Kin_Rank'];
			$organization_id = $Database['Company_ID'];

			// manipulation zone

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
			}
	});

?>
