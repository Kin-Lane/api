<?php

$route = '/api/';	
$app->post($route, function () use ($app){
	
	$Add = 1;
	$ReturnObject = array();
	
 	$request = $app->request(); 
 	$param = $request->params();	

	if(isset($_POST['name'])){ $name = $param['name']; } else { $name = ''; }
	if(isset($_POST['about'])){ $about = $param['about']; } else { $about = ''; }
	if(isset($_POST['rank'])){ $rank = $param['rank']; } else { $rank = 7; }
	if(isset($_POST['organization_id'])){ $organization_id = $param['organization_id']; } else { $organization_id = 0; }		

  	$Query = "SELECT * FROM api WHERE Name = '" . $name . "'";
	//echo $Query . "<br />";
	$Database = mysql_query($Query) or die('Query failed: ' . mysql_error());
	
	if($Database && mysql_num_rows($Database))
		{	
		$ThisItem = mysql_fetch_assoc($Database);	
		}
	else 
		{
		$Query = "INSERT INTO api(Name,About,Kin_Rank,Company_ID) VALUES('" . mysql_real_escape_string($name) . "','" . mysql_real_escape_string($about) . "'," . mysql_real_escape_string($rank) . "," . mysql_real_escape_string($organization_id) . ")";
		//echo $query . "<br />";
		mysql_query($Query) or die('Query failed: ' . mysql_error());
		$api_id = mysql_insert_id();				
		}

	$host = $_SERVER['HTTP_HOST'];		
	$api_id =  encrypt($api_id,$host);		
	
	$F = array();
	$F['api_id'] = $api_id;
	$F['name'] = $name;
	$F['about'] = $about;
	$F['rank'] = $rank;
	$F['organization_id'] = $organization_id;
	
	array_push($ReturnObject, $F);
		
	$app->response()->header("Content-Type", "application/json");
	echo stripslashes(format_json(json_encode($ReturnObject)));

	});
	
?>