<?php
$route = '/api/:api_id/';	
$app->put($route, function ($api_id) use ($app){
				
	$host = $_SERVER['HTTP_HOST'];		
	$api_id =  encrypt($api_id,$host);				
		
	$ReturnObject = array();
	
 	$request = $app->request(); 
 	$_POST = $request->params();	
	
	$host = $_SERVER['HTTP_HOST'];		
	$api_id = decrypt($api_id,$host);		

	if(isset($_POST['name'])){ $name = $_POST['name']; } elseif(isset($_POST['name'])){ $name = $_REQUEST['name']; } else { $name = ''; }
	if(isset($_POST['description'])){ $description = $_POST['description']; } elseif(isset($_POST['description'])){ $description = $_REQUEST['description']; } else { $description = ''; }
	if(isset($_POST['url'])){ $url = $_POST['url']; } elseif(isset($_POST['url'])){ $url = $_REQUEST['url']; } else { $url = ''; }
	if(isset($_POST['tags'])){ $tags = $_POST['tags']; } elseif(isset($_POST['tags'])){ $tags = $_REQUEST['tags']; } else { $tags = ''; }	

  	$Query = "SELECT * FROM blog WHERE ID = " . $api_id;
	//echo $Query . "<br />";
	$Database = mysql_query($Query) or die('Query failed: ' . mysql_error());
	
	if($Database && mysql_num_rows($Database))
		{	
		$query = "UPDATE blog SET";

		$query .= " name = '" . mysql_real_escape_string($name) . "'";
		
		if($description!='') { $query .= ", description = '" . mysql_real_escape_string($description) . "'"; }
		if($description!='') { $query .= ", url = '" . mysql_real_escape_string($url) . "'"; }
		if($description!='') { $query .= ", tags = '" . mysql_real_escape_string($tags) . "'"; }
		
		$query .= " WHERE slug = '" . $slug . "'";
		
		//echo $query . "<br />";
		mysql_query($query) or die('Query failed: ' . mysql_error());	
		}

	$F = array();
	$F['name'] = $name;
	$F['url'] = $url;
	$F['tags'] = $tags;
	$F['slug'] = $slug;
	
	array_push($ReturnObject, $F);
		
	$app->response()->header("Content-Type", "application/json");
	echo stripslashes(format_json(json_encode($ReturnObject)));

	});
?>