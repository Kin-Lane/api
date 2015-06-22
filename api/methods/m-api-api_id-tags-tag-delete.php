<?php
$route = '/api/:api_id/tags/:tag';
$app->delete($route, function ($api_id,$Tag)  use ($app){

	$host = $_SERVER['HTTP_HOST'];		
	$api_id = prepareIdIn($api_id,$host);

	$ReturnObject = array();
		
 	$request = $app->request(); 
 	$param = $request->params();	
	
	if(isset($param['tag']))
		{
		$tag = trim(mysql_real_escape_string($param['tag']));
			
		$CheckTagQuery = "SELECT Tag_ID FROM tags where Tag = '" . $tag . "'";
		$CheckTagResults = mysql_query($CheckTagQuery) or die('Query failed: ' . mysql_error());		
		if($CheckTagResults && mysql_num_rows($CheckTagResults))
			{
			$Tag = mysql_fetch_assoc($CheckTagResults);		
			$tag_id = $Tag['Tag_ID'];

			$DeleteQuery = "DELETE FROM api_tag_pivot where Tag_ID = " . trim($tag_id) . " AND API_ID = " . trim($api_id);
			$DeleteResult = mysql_query($DeleteQuery) or die('Query failed: ' . mysql_error());
			}

		$tag_id = prepareIdOut($tag_id,$host);

		$F = array();
		$F['tag_id'] = $tag_id;
		$F['tag'] = $tag;
		$F['api_count'] = 0;
		
		array_push($ReturnObject, $F);

		}		

		$app->response()->header("Content-Type", "application/json");
		echo stripslashes(format_json(json_encode($ReturnObject)));
	});
?>