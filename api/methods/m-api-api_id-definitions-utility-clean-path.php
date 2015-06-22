<?php
$route = '/api/:api_id/definitions/utility/clean/path/';
$app->get($route, function ($api_id)  use ($app,$awsAccessKey,$awsSecretKey,$awsSiteBucket,$awsRootURL,$guser,$gpass){
	
	$host = $_SERVER['HTTP_HOST'];
	$api_id = prepareIdIn($api_id,$host);	
	
	$ReturnObject = array();
		
 	$request = $app->request(); 
 	$param = $request->params();	
	
	$DefinitionQuery = "SELECT * FROM api_swagger WHERE API_ID = " . $api_id . " ORDER BY import_date DESC";
	//echo $DefinitionQuery . chr(13);
	$DefinitionResult = mysql_query($DefinitionQuery) or die('Query failed: ' . mysql_error());
	
	if($DefinitionResult && mysql_num_rows($DefinitionResult))
		{			
		$Definition = mysql_fetch_assoc($DefinitionResult);		

		$API_Swagger_ID = $Definition['API_Swagger_ID'];
		$swagger = $Definition['swagger'];
		$info_title = $Definition['info_title'];
		$API_Name_Slug = PrepareFileName($info_title);
		
		$SwaggerPathQuery = "SELECT * FROM api_swagger_paths WHERE API_Swagger_ID = " . $API_Swagger_ID . " ORDER BY path,pathtype";
		//echo $SwaggerPathQuery . "<br />";
		$SwaggerPathResults = mysql_query($SwaggerPathQuery) or die('Query failed: ' . mysql_error());
		
		if($SwaggerPathResults && mysql_num_rows($SwaggerPathResults))
			{	
			while ($SwaggerPathRow = mysql_fetch_assoc($SwaggerPathResults))
				{				
				$P = array();					
				$Swagger_Path_ID = $SwaggerPathRow['ID'];	
				$path = $SwaggerPathRow['path'];
				$topath = $path;
				$firstchar = substr($topath,0,1);
				if($firstchar!='/')
					{
					$topath = "/" . $topath; 
					}
				$lastchar = substr($topath,strlen($topath)-1,strlen($topath));
				if($lastchar!='/')
					{
					$topath .= "/";
					}					
					
				$P['from'] = $path;
				$P['to'] = $topath;
				array_push($ReturnObject, $P);
				
				$UpdateQuery = "UPDATE api_swagger_paths SET path = '" . $topath . "' WHERE ID = " . $Swagger_Path_ID;
				//echo $UpdateQuery . chr(13);
				$UpdateResult = mysql_query($UpdateQuery) or die('Query failed: ' . mysql_error());				
							
				}
			}
		}

	$app->response()->header("Content-Type", "application/json");
	echo stripslashes(format_json(json_encode($ReturnObject)));	

	});	
?>