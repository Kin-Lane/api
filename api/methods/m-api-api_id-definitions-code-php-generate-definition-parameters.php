<?php
$route = '/api/:api_id/definitions/code/php/generate/definition/parameters/';
$app->get($route, function ($api_id)  use ($app){
	
	$host = $_SERVER['HTTP_HOST'];		
	$api_id = decrypt($api_id,$host);	
	
	$ReturnObject = array();
		
 	$request = $app->request(); 
 	$param = $request->params();
	
	if(isset($_REQUEST['filterobject'])){ $filterobject = $_REQUEST['filterobject']; } else { $filterobject = '';}
	
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

		$SwaggerDefinitionQuery = "SELECT * FROM api_swagger_definitions WHERE API_Swagger_ID = " . $API_Swagger_ID;
		//echo $SwaggerDefinitionQuery . "<br />";
		$SwaggerDefinitionResults = mysql_query($SwaggerDefinitionQuery) or die('Query failed: ' . mysql_error());
		
		if($SwaggerDefinitionResults && mysql_num_rows($SwaggerDefinitionResults))
			{	
			while ($SwaggerDefinition = mysql_fetch_assoc($SwaggerDefinitionResults))
				{
				$Definition_ID = $SwaggerDefinition['ID'];	
				$definition_name = $SwaggerDefinition['name'];			
				//echo $definition_name;
				$DefinitionArray[$definition_name] = array();				
				$DefinitionPropertiesArray['properties'] = array();
	
				$SwaggerDefinitionPropertyQuery = "SELECT * FROM api_swagger_definition_properties WHERE Swagger_Definition_ID = " . $Definition_ID;
				//echo $SwaggerDefinitionPropertyQuery . "<br />";
				$SwaggerDefinitionPropertyResult = mysql_query($SwaggerDefinitionPropertyQuery) or die('Query failed: ' . mysql_error());
				
				if($SwaggerDefinitionPropertyResult && mysql_num_rows($SwaggerDefinitionPropertyResult))
					{	
					while ($SwaggerDefinitionProperty = mysql_fetch_assoc($SwaggerDefinitionPropertyResult))
						{
						$Swagger_Definition_Property_ID = $SwaggerDefinitionProperty['ID'];	
						$property_name = $SwaggerDefinitionProperty['name'];
						$property_description = $SwaggerDefinitionProperty['description'];
						$property_type = $SwaggerDefinitionProperty['fieldtype'];
						$property_format = $SwaggerDefinitionProperty['fieldformat'];	
						
						if($filterobject != '')
							{
							//echo $definition_name . " = " . $filterobject . ""
							if($definition_name == $filterobject)
								{
								$Parameters .= chr (36) . $property_name . " = " . chr(36) . "Object['" . $property_name . "'];";
								}										
							}
						else 
							{
							$Parameters .= chr (36) . $property_name . " = " . chr(36) . "Object['" . $property_name . "'];";								
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