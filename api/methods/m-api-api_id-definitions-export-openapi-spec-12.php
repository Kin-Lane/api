<?php
$route = '/api/:api_id/definitions/export/openapi-spec/1.2/';
$app->get($route, function ($api_id)  use ($app,$awsAccessKey,$awsSecretKey,$awsSiteBucket,$awsRootURL){

	$host = $_SERVER['HTTP_HOST'];		
	$api_id = prepareIdIn($api_id,$host);

	$ReturnObject = array();
		
 	$request = $app->request(); 
 	$param = $request->params();
	
	$ReturnSwagger = "";
	
	$swagger = "";
	$info_title = "";
	$info_description = "";
	$info_termsOfService = "";
	$info_contact_name = "";
	$info_contact_url = "";
	$info_contact_email = "";
	$info_license_name = "";
	$info_license_url = "";
	$info_version = "";
	$host = "";
	$basePath = "";
	$schemes = "";
	$consumes = "";
	$produces = "";
	
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
		$resourcePath = $Definition['host'];
		$schemes = $Definition['schemes'];
		$consumes = $Definition['consumes'];
		$produces = $Definition['produces'];		

		$Swagger = array();
		$Swagger['apiVersion'] = "1.0.0";
		$Swagger['swaggerVersion'] = "1.2";

		$Swagger['basePath'] = $resourcePath;
		$Swagger['resourcePath'] = $basePath;			
			
		//$SchemesArray = array('http');		
		//$Swagger['schemes'] = $SchemesArray;
	
		$ProducesArray = array('application/json');		
		$Swagger['produces'] = $ProducesArray;
		
		$ProducesArray = array('application/json');		
		$Swagger['consumes'] = $ProducesArray;	
			
		$Paths = array();	
		$SwaggerPath = array();
	
		$LastType = "";

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
				
				if($LastType=="")
					{
					$SwaggerPathType["path"] = $path; 	
					$SwaggerPathType["operations"] = array();
					}		
				
				$SwaggerPathTypeDetail = array();
				$SwaggerPathTypeDetail['method'] = $type;
				$SwaggerPathTypeDetail['summary'] = $operation_summary;
				$SwaggerPathTypeDetail['notes'] = $operation_description;
				$SwaggerPathTypeDetail['nickname'] = $operation_operationId;
				
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
						
						$SwaggerPathTypeField = array();
						$SwaggerPathTypeField['paramType'] = $field_in;
						$SwaggerPathTypeField['name'] = $field_name;	
						if($field_description!='')
							{				
							$SwaggerPathTypeField['description'] = $field_description;
							}
						
						if($field_required!=='1') { $field_required = true; } else  { $field_required = false; }
						//$SwaggerPathTypeField['required'] = $field_required;
						
						if($field_type!='' && $field_in != 'body')
							{					
							$SwaggerPathTypeField['type'] = strtolower($field_type);					
							//$SwaggerPathTypeField['format'] = $field_format;
							$SwaggerPathTypeField['default'] = $field_default;
							}					
						
						array_push($SwaggerPathTypeDetail['parameters'], $SwaggerPathTypeField);
						unset($SwaggerPathTypeField);
						}
					}

				$ResponsesArray = array();
				
				$SwaggerPathResponsesQuery = "SELECT * FROM api_swagger_path_responses WHERE Swagger_Path_ID = " . $Swagger_Path_ID;
				//echo $SwaggerPathResponsesQuery . "<br />";
				$SwaggerPathResponsesResults = mysql_query($SwaggerPathResponsesQuery) or die('Query failed: ' . mysql_error());
				
				if($SwaggerPathResponsesResults && mysql_num_rows($SwaggerPathResponsesResults))
					{	
					while ($SwaggerPathResponse = mysql_fetch_assoc($SwaggerPathResponsesResults))
						{
						$Swagger_Path_Field_ID = $SwaggerPathResponse['ID'];	
						$response_name = $SwaggerPathResponse['name'];
						$response_description = $SwaggerPathResponse['description'];
						
						$R = array();					
						$R['code'] = $response_name;
						$R['message'] = $response_description;																	
		
						array_push($ResponsesArray, $R);
						
						unset($R);
						}
					}
				else 
					{	
					$response_name = "200";
					$response_description = "OK";
	
					$R = array();					
					$R['code'] = $response_name;
					$R['message'] = $response_description;																	
	
					array_push($ResponsesArray, $R);
					
					unset($R);								
					}
				
				foreach ($ResponsesArray as $key => $value)
					{
					$SwaggerPathTypeDetail['responses'] = $value;
					}									
																					
				if($LastType==$path || $LastType == "")
					{
					//echo $LastType . " == " . $path . chr(13);
					//var_dump($SwaggerPathTypeDetail);
					//echo "<hr />";
					array_push($SwaggerPathType["operations"], $SwaggerPathTypeDetail);	
					}
				else				
					{
					//echo $LastType . " != " . $path . chr(13);
					//var_dump($SwaggerPathTypeDetail);
					//echo "<hr />";					
					array_push($SwaggerPathType["operations"], $SwaggerPathTypeDetail);	
					
					array_push($SwaggerPath, $SwaggerPathType);									
					array_push($Paths, $SwaggerPath);
					
					$SwaggerPathType["path"] = $path; 	
					$SwaggerPathType["operations"] = array();
										
					}
					
				$LastType = $path;	
																		
				}
			}
	
		foreach ($Paths as $key => $value)
			{
			$Swagger['apis'] = $value;
			}
	
		$DefinitionArray = array();	
			
		$SwaggerDefinitionQuery = "SELECT * FROM api_swagger_definitions WHERE API_Swagger_ID = " . $API_Swagger_ID;
		//echo $SwaggerPathFieldQuery . "<br />";
		$SwaggerDefinitionResults = mysql_query($SwaggerDefinitionQuery) or die('Query failed: ' . mysql_error());
		
		if($SwaggerDefinitionResults && mysql_num_rows($SwaggerDefinitionResults))
			{	
			while ($SwaggerDefinition = mysql_fetch_assoc($SwaggerDefinitionResults))
				{
				$Definition_ID = $SwaggerDefinition['ID'];	
				$definition_name = $SwaggerDefinition['name'];			
	
				$DefinitionArray[$definition_name] = array();
				$DefinitionPropertiesArray['id'] = $definition_name;			
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
						
						$DefinitionPropertyArray[$property_name] = array();
						
						//if($property_type=='') { $property_type = "string"; }
						
						$C = array();
						if($property_type!='') 
							{					
							$C['description'] = $property_description;
							}
						else
							{					
							$C['description'] = "This is a default description.";
							}
							
						if($property_type!='') 
							{
							$C['type'] = $property_type;
							}
						else
							{
							$C['type'] = 'string';
							}						
	
						$DefinitionPropertyArray[$property_name] = $C;	
						
						}
					}
				
				$object = new stdClass();	
				foreach ($DefinitionPropertiesArray as $key => $value)
					{
					$object = $value;
					}					
					
				$DefinitionPropertiesArray['properties'] = $DefinitionPropertyArray;			
				unset($C);				

				$DefinitionArray[$definition_name] = $DefinitionPropertiesArray;
				
				unset($DefinitionPropertiesArray);
					
				}
			}	
	
		if(count($DefinitionArray)>1)
			{
			$Swagger['models'] = array();		
			foreach ($DefinitionArray as $key => $value)
				{
				$Swagger['models']->$key = $value;
				}
			}
	
		$Swagger['models'] = $DefinitionArray;
		
		}

	$Swagger_JSON = stripslashes(format_json(json_encode($Swagger)));
	
	$export_file_name = PrepareFileName($info_title);
	$export_file_name = $export_file_name . "-" . date('Y-m-d-H-i-s') . ".json";	
	
	$local_file = "/var/www/html/kin_lane/api/api/temp/" . $export_file_name;
	
	$tmpFile = fopen($local_file, "w") or die("Unable to open file!");
	fwrite($tmpFile, $Swagger_JSON);
	fclose($tmpFile);	
	
	//instantiate the class
	$s3 = new S3($awsAccessKey, $awsSecretKey);

	$fileName = "kin-lane/stack/api/export/" . $export_file_name;
	
	//echo $awsSiteBucket . "<br />";
	//echo $fileName . "<br />";
	//echo $fileTempName . "<br />";
	
	//move the file
	if ($s3->putObjectFile($local_file, $awsSiteBucket, $fileName, S3::ACL_PUBLIC_READ)) {
		//echo "<strong>We successfully uploaded your file.</strong> - " . $awsSiteBucket . " - " . $fileName ;
	}else{
		//echo "<strong>Something went wrong while uploading your file... sorry.</strong>";
	}
	
	$Swagger_Path = $awsRootURL . $fileName;	
	
	$ReturnObject['url'] = $Swagger_Path; 
	
	$app->response()->header("Content-Type", "application/json");
	echo stripslashes(format_json(json_encode($ReturnObject)));		

	unlink($local_file);

	});
?>