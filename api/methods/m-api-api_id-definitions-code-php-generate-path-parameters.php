<?php
$route = '/api/:api_id/definitions/code/php/generate/path/parameters/';
$app->get($route, function ($api_id)  use ($app){
	
	$host = $_SERVER['HTTP_HOST'];		
	$api_id = prepareIdIn($api_id,$host);
	
	$ReturnObject = array();
		
 	$request = $app->request(); 
 	$param = $request->params();
	
	if(isset($_REQUEST['filterpath'])){ $filterpath = $_REQUEST['filterpath']; } else { $filterpath = '';}
	if(isset($_REQUEST['filterverb'])){ $filterverb = $_REQUEST['filterverb']; } else { $filterverb = '';}
	
	$Parameters = "";
	
	$DefinitionQuery = "SELECT * FROM api_swagger WHERE API_ID = " . $api_id . " ORDER BY import_date DESC";
	//echo $DefinitionQuery . chr(13);
	$DefinitionResult = mysql_query($DefinitionQuery) or die('Query failed: ' . mysql_error());
	
	if($DefinitionResult && mysql_num_rows($DefinitionResult))
		{
						
		$Definition = mysql_fetch_assoc($DefinitionResult);		

		$swagger = $Definition['swagger'];
		$info_title = $Definition['info_title'];
		$info_description = $Definition['info_description'];
		$info_termsOfService = $Definition['info_termsOfService'];
		$info_contact_name = $Definition['info_contact_name'];
		$info_contact_url = $Definition['info_contact_url'];
		$info_contact_email = $Definition['info_contact_email'];
		
		$info_license_name = $Definition['info_license_name'];
		$info_license_url = $Definition['info_license_url'];
		$info_version = $Definition['info_version'];
		$host = $Definition['host'];
		$basePath = $Definition['basePath'];
		$schemes = $Definition['schemes'];
		$consumes = $Definition['consumes'];
		$produces = $Definition['produces'];		

		$SwaggerPathQuery = "SELECT * FROM api_swagger_paths WHERE API_Swagger_ID = " . $API_Swagger_ID . " ORDER BY path,pathtype";
		//echo $SwaggerPathQuery . "<br />";
		$SwaggerPathResults = mysql_query($SwaggerPathQuery) or die('Query failed: ' . mysql_error());
		
		if($SwaggerPathResults && mysql_num_rows($SwaggerPathResults))
			{	
			while ($SwaggerPathRow = mysql_fetch_assoc($SwaggerPathResults))
				{
					
				$Swagger_Path_ID = $SwaggerPathRow['ID'];	
				$path = $SwaggerPathRow['path'];
				$type = strtolower($SwaggerPathRow['pathtype']);
				$operation_summary = $SwaggerPathRow['operation_summary'];
				$operation_description = $SwaggerPathRow['operation_description'];
				$operation_operationId = $SwaggerPathRow['operation_operationId'];
				$schemas = $SwaggerPathRow['pathschemas'];					
				$tags = $SwaggerPathRow['tags'];
				
				$SwaggerPathFieldQuery = "SELECT * FROM api_swagger_path_fields WHERE Swagger_Path_ID = " . $Swagger_Path_ID . " ORDER BY name";
				//echo $SwaggerPathFieldQuery . "<br />";
				$SwaggerPathFieldResults = mysql_query($SwaggerPathFieldQuery) or die('Query failed: ' . mysql_error());
				
				if($SwaggerPathFieldResults && mysql_num_rows($SwaggerPathFieldResults))
					{
						
					$SwaggerPathTypeDetail['parameters'] = array();	
							
					while ($SwaggerPathField = mysql_fetch_assoc($SwaggerPathFieldResults))
						{
						$Swagger_Path_Field_ID = $SwaggerPathField['ID'];	
						
						$field_name = $SwaggerPathField['name'];
						$field_in = $SwaggerPathField['fieldin'];
						
						$field_description = $SwaggerPathField['description'];
						$field_required = $SwaggerPathField['required'];
						
						$field_type = $SwaggerPathField['fieldtype'];
						$field_format = $SwaggerPathField['fieldformat'];
											
						$field_default = $SwaggerPathField['fielddefault'];	
						
						if($filterpath != '')
							{
							if($filterverb != '')
								{
								if($path == $filterpath && $type == $filterverb)
									{
									$Parameters .= chr (36) . $field_name . " = " . chr(36) . "Object['" . $field_name . "'];" . chr(10).chr(13);	
									}	
								}	
							else								
								{
								if($path == $filterpath)
									{
									$Parameters .= chr (36) . $field_name . " = " . chr(36) . "Object['" . $field_name . "'];" . chr(10).chr(13);	
									}										
								}
							}
						else 
							{
							$Parameters .= chr (36) . $field_name . " = " . chr(36) . "Object['" . $field_name . "'];" . chr(10).chr(13);								
							}
						}
					}								
				}
			}		
		}

	$ReturnObject['content'] = $Parameters;
	
	$app->response()->header("Content-Type", "application/json");
	echo stripslashes(format_json(json_encode($ReturnObject)));		

	});
	
?>