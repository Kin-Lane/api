<?php
$route = '/api/:api_id/definitions/import/swagger/2.0/';
$app->post($route, function ($api_id)  use ($app){

	$api_id = str_replace(" ","+",$api_id);
	$api_id =  str_replace("~","/",$api_id);	
	//echo 'API_ID: ' . $api_id . "<br />";
	$host = $_SERVER['HTTP_HOST'];
	//echo $host . "<br />";		
	$api_id = decrypt($api_id,$host);
	//echo 'API_ID: ' . $api_id . "<br />";

	$ReturnObject = array();
		
 	$request = $app->request(); 
 	$param = $request->params();	
	
	$import_date = date('Y-m-d H:i:s');
	$import_id = $param['appid'];	
	$api_definition_url = $param['url'];		
	
	$ObjectText = file_get_contents($api_definition_url);
	//echo $ObjectText;
	$ObjectResult = json_decode($ObjectText,true);	  
 	
	$SwaggerVersion = "2.0";
	if(isset($SwaggerInfo['swagger']))
		{
		$SwaggerVersion = $ObjectResult['swagger'];
		}
			
	$SwaggerBasePath = "/";
	if(isset($SwaggerInfo['basePath']))
		{
		$SwaggerBasePath = $ObjectResult['basePath'];
		}	
	
	$SwaggerResourcePath = "";
	if(isset($SwaggerInfo['host']))
		{
		$SwaggerResourcePath = $ObjectResult['host'];
		}		
	
	$SwaggerInfo = "";
	if(isset($SwaggerInfo['info']))
		{
		$SwaggerInfo = $ObjectResult['info'];
		}	
			
	$SwaggerInfo_Version = "";
	if(isset($SwaggerInfo['version']))
		{
		$SwaggerInfo_Version = $SwaggerInfo['version'];
		}	
			
	$SwaggerInfo_Title = "No Title";
	if(isset($SwaggerInfo['title']))
		{
		$SwaggerInfo_Title = $SwaggerInfo['title'];
		}	
			
	$SwaggerInfo_Description = "";
	if(isset($SwaggerInfo['description']))
		{
		$SwaggerInfo_Description = $SwaggerInfo['description'];
		}		
	
	$SwaggerInfo_TermsOfService = "";
	if(isset($SwaggerInfo['termsOfService']))
		{
		$SwaggerInfo_TermsOfService = $SwaggerInfo['termsOfService'];
		}
	
	if(isset($SwaggerInfo['contact']))
		{
		if(isset($SwaggerInfo['contact']['name']))
			{
			$SwaggerInfo_Contact_Name = $SwaggerInfo['contact']['name'];
			}
		else
			{
			$SwaggerInfo_Contact_Name = "";
			}
		if(isset($SwaggerInfo['contact']['email']))
			{
			$SwaggerInfo_Contact_Email = $SwaggerInfo['contact']['email'];
			}
		else
			{
			$SwaggerInfo_Contact_Email = "";
			}
		if(isset($SwaggerInfo['contact']['url']))
			{
			$SwaggerInfo_Contact_URL = $SwaggerInfo['contact']['url'];
			}
		else
			{
			$SwaggerInfo_Contact_URL = "";
			}			
		}
	else
		{
		$SwaggerInfo_Contact_Name = "";
		$SwaggerInfo_Contact_URL = "";
		$SwaggerInfo_Contact_Email = "";
		}								
	
	if(isset($SwaggerInfo['security']))
		{	
		$SwaggerSecurity = $ObjectResult['security'];
		}
	else
		{	
		$SwaggerSecurity = "";
		}		
		
	$SwaggerAPIs = array();
	if(isset($ObjectResult['paths']))
		{
		$SwaggerAPIs = $ObjectResult['paths'];
		}	
		
	$SwaggerModels = array();
	if(isset($ObjectResult['definitions']))
		{
		$SwaggerModels = $ObjectResult['definitions'];
		}

	//echo "Title: " . $SwaggerInfo_Title . "<br />";
	//echo "Base Path: " . $SwaggerBasePath . "<br />";
	//echo "Contact Name: " . $SwaggerInfo_Contact_Name . "<br />";
	//cho "Contact URL: " . $SwaggerInfo_Contact_URL . "<br />";
	
	$InsertQuery = "INSERT INTO api_swagger(";
	
	$InsertQuery .= "API_ID,";
	
	$InsertQuery .= "import_date,";
	$InsertQuery .= "import_id,";
	
	$InsertQuery .= "swagger,";
	$InsertQuery .= "info_title,";
	$InsertQuery .= "info_description,";
	$InsertQuery .= "info_termsOfService,";
	
	$InsertQuery .= "info_version,";
	
	$InsertQuery .= "info_contact_name,";
	$InsertQuery .= "info_contact_url,";
	$InsertQuery .= "info_contact_email,";
	
	$InsertQuery .= "host,";
	$InsertQuery .= "basePath";
	
	$InsertQuery .= ") VALUES(";
	
	$InsertQuery .= mysql_real_escape_string($api_id) . ",";
	
	$InsertQuery .= "'" . mysql_real_escape_string($import_date) . "',";
	$InsertQuery .= "'" . mysql_real_escape_string($import_id) . "',";
	
	$InsertQuery .= "'2.0',";
	$InsertQuery .= "'" . mysql_real_escape_string($SwaggerInfo_Title) . "',";
	$InsertQuery .= "'" . mysql_real_escape_string($SwaggerInfo_Description) . "',";
	$InsertQuery .= "'" . mysql_real_escape_string($SwaggerInfo_TermsOfService) . "',";
	
	$InsertQuery .= "'" . mysql_real_escape_string($SwaggerInfo_Version) . "',";

	$InsertQuery .= "'" . mysql_real_escape_string($SwaggerInfo_Contact_Name) . "',";
	$InsertQuery .= "'" . mysql_real_escape_string($SwaggerInfo_Contact_URL) . "',";
	$InsertQuery .= "'" . mysql_real_escape_string($SwaggerInfo_Contact_Email) . "',";
	
	$InsertQuery .= "'" . mysql_real_escape_string($SwaggerResourcePath) . "',";
	$InsertQuery .= "'" . mysql_real_escape_string($SwaggerBasePath) . "'";
	$InsertQuery .= ")";
	
	echo $InsertQuery . "<br />";
	mysql_query($InsertQuery) or die('Query failed: ' . mysql_error());				
	$api_definition_id = mysql_insert_id();		

	foreach($SwaggerAPIs as $key => $value)
		{
	
		$path = $key;
		//echo "<br />Path: " . $path . "<br />";		
		
		foreach($value as $key2 => $operation)
			{
			//var_dump($operation);
			$method = $key2;
			$type = $method;
			
			$operation_summary = "";
			if(isset($operation['summary']))
				{
				$operation_summary = $operation['summary'];
				}
							
			$operation_description = "";
			if(isset($operation['description']))
				{
				$operation_description = $operation['description'];
				}
			$operation_operationId = PrepareFileName($operation_description);
			if(isset($operation['operationId']))
				{				
				$operation_operationId = $operation['operationId'];
				}

			if($path!='')
				{

				$SwaggerPathQuery = "SELECT * FROM api_swagger_paths WHERE API_Swagger_ID = " . $api_definition_id . " AND path = '" . $path . "'  AND pathtype = '" . $type . "'";
				//echo $SwaggerQuery . "<br />";
				$SwaggerPathResults = mysql_query($SwaggerPathQuery) or die('Query failed: ' . mysql_error());
				
				if($SwaggerPathResults && mysql_num_rows($SwaggerPathResults))
					{
					$SwaggerPath = mysql_fetch_assoc($SwaggerPathResults);		
					$Swagger_Path_ID = $SwaggerPath['ID'];
					}
				else 
					{							
					$InsertQuery = "INSERT INTO api_swagger_paths(";
					
					$InsertQuery .= "API_Swagger_ID,";
					$InsertQuery .= "path,";
					$InsertQuery .= "pathtype,";
					$InsertQuery .= "operation_summary,";
					$InsertQuery .= "operation_description,";
					$InsertQuery .= "operation_operationId";
					
					$InsertQuery .= ") VALUES(";
					
					$InsertQuery .= $api_definition_id . ",";
					$InsertQuery .= "'" . $path . "',";
					$InsertQuery .= "'" . $type . "',";
					$InsertQuery .= "'" . mysql_real_escape_string($operation_summary) . "',";
					$InsertQuery .= "'" . mysql_real_escape_string($operation_description) . "',";
					$InsertQuery .= "'" . mysql_real_escape_string($operation_operationId) . "'";
					
					$InsertQuery .= ")";
					//echo $InsertQuery;
					mysql_query($InsertQuery) or die('Query failed: ' . mysql_error());
					$Swagger_Path_ID = mysql_insert_id();		
					}				
				
				$parameters = "";
				if(isset($operation['parameters']))
					{
					$parameters = $operation['parameters'];	
					}				
				$responseMessages = "";
				if(isset($operation['responses']))
					{
					$responseMessages = $operation['responses'];
					}					
				
				//echo "dmethod: " . $method . "<br />";		
				//echo "summary: " . $operation_summary . "<br />";		
				//echo "notes: " . $notes . "<br />";		
				//echo "nickname: " . $nickname . "<br />";
				//echo "type: " . $type . "<br />";	

				//var_dump($parameters);
				//echo "Here: " . count($parameters) . "<br />";
				if(is_array($parameters))
					{
				foreach($parameters as $parameter)
					{
						
					$parameter_name = $parameter['name'];
										
					if(isset($parameter['id']))
						{
						$parameter_id = $parameter['id'];	
						}
					else 
						{
						$parameter_id = PrepareFileName($parameter_name);
						}	
					if(isset($parameter['description']))
						{
						$parameter_description = $parameter['description'];	
						}
					else 
						{
						$parameter_description = "";
						}	
					if(isset($parameter['required']))
						{
						$parameter_required = $parameter['required'];	
						}
					else 
						{
						$parameter_required = 0;
						}
					if($parameter_required==''){ $parameter_required = 0; }
															
					if(isset($parameter['allowMultiple']))
						{
						$parameter_allowMultiple = $parameter['allowMultiple'];	
						}
					else 
						{
						$parameter_allowMultiple = "";
						}
					if(isset($parameter['dataType']))
						{
						$parameter_dataType = $parameter['dataType'];	
						}
					else 
						{
						$parameter_dataType = "";
						}
					if(isset($parameter['in']))
						{
						$parameter_paramType = $parameter['in'];	
						}
					else 
						{
						$parameter_paramType = "query";
						}						
					
					//echo "id: " . $parameter_id . "<br />";
					//echo "name: " . $parameter_name . "<br />";
					//echo "description: " . $parameter_description . "<br />";
					//echo "required: " . $parameter_required . "<br />";
					//echo "allowMultiple: " . $parameter_allowMultiple . "<br />";
					//echo "datatype: " . $parameter_dataType . "<br />";
					//echo "paramType: " . $parameter_paramType . "<br />";			
				
					$SwaggerQuery = "SELECT * FROM api_swagger_path_fields WHERE Swagger_Path_ID = " . $Swagger_Path_ID . " AND name = '" . mysql_real_escape_string($parameter_name) . "' AND fieldin = '" . $parameter_paramType . "'";
					//echo $SwaggerQuery . "<br />";
					$SwaggerPathFieldsResults = mysql_query($SwaggerQuery) or die('Query failed: ' . mysql_error());
					
					if(mysql_num_rows($SwaggerPathFieldsResults)==0)
						{	
						
						$InsertQuery = "INSERT INTO api_swagger_path_fields(Swagger_Path_ID,name,fieldin,description,required,fieldtype) VALUES(" . $Swagger_Path_ID . ",'" . mysql_real_escape_string($parameter_name) . "','" . $parameter_paramType . "','" . mysql_real_escape_string($parameter_description) . "'," . $parameter_required . ",'" . $parameter_dataType . "')";
						//echo "field:" . $InsertQuery;
						mysql_query($InsertQuery) or die('Query failed: ' . mysql_error());	
						}					
					
					}				

				foreach($responseMessages as $key => $value)
					{
					//echo $key . "<br />";
					//var_dump($value);
					
					$response_code = $key;					
					$response_description = $value['description'];
					
					if(isset($value['schema']['items']))
						{
						$response_schema = $value['schema']['items']['$ref'];
						//var_dump($response_schema);
						}
					else
						{
						$response_schema = "";	
						}
					//echo "code: " . $response_code . "<br />";
					//echo "desc: " . $response_description . "<br />";
					//echo "schema: " . $response_schema . "<br />";						
					}	
				}
	
				}	
			}			
		}			
	
	// Data Models
	if(isset($SwaggerModels))
		{
		foreach($SwaggerModels as $key => $value)
			{
				
			$name = $key;	
				
			if(isset( $value['id']))
				{
				$model_id = $value['id'];		
				}
			else 
				{
				$model_id = 0;
				}
			//echo "id: " . $model_id . "<br />";
			if(isset( $value['properties']))
				{			
				$model_properties = $value['properties'];
			
				$SwaggerDefinitionQuery = "SELECT * FROM api_swagger_definitions WHERE API_Swagger_ID = " . $api_definition_id . " AND name = '" . $name . "'";
				//echo $SwaggerDefinitionQuery . "<br />";
				$SwaggerDefinitionResults = mysql_query($SwaggerDefinitionQuery) or die('Query failed: ' . mysql_error());
				if($SwaggerDefinitionResults && mysql_num_rows($SwaggerDefinitionResults))
					{
					$SwaggerDefinition = mysql_fetch_assoc($SwaggerDefinitionResults);		
					$Swagger_Definition_ID = $SwaggerDefinition['ID'];
					}
				else 
					{				
					$InsertQuery = "INSERT INTO api_swagger_definitions(API_Swagger_ID,name) VALUES(" . $api_definition_id . ",'" . $name . "')";
					//echo "def:" .  $InsertQuery;
					mysql_query($InsertQuery) or die('Query failed: ' . mysql_error());
					$Swagger_Definition_ID = mysql_insert_id();	
					}			
							
				foreach($model_properties as $key => $value)
					{
					$property_name = $key;
					
					if(isset($value['type']))
						{
						$property_type = $value['type'];
						
						$SwaggerPropertyQuery = "SELECT * FROM api_swagger_definition_properties WHERE Swagger_Definition_ID = " . $Swagger_Definition_ID . " AND name = '" . $property_name . "'";
						//echo $SwaggerPropertyQuery . "<br />";
						$SwaggerPropertyResults = mysql_query($SwaggerPropertyQuery) or die('Query failed: ' . mysql_error());
						
						if(mysql_num_rows($SwaggerPropertyResults)==0)
							{	
							
							$InsertQuery = "INSERT INTO api_swagger_definition_properties(Swagger_Definition_ID,name,fieldtype) VALUES(" . $Swagger_Definition_ID . ",'" . $property_name . "','" . $type . "')";
							//echo $InsertQuery;
							mysql_query($InsertQuery) or die('Query failed: ' . mysql_error());	
							}				
						}
					}
				}
			}																	
		}

	$ReturnObject['version'] = "Import of Swagger 2.0 (" . $api_definition_url . ") successful.";

	$app->response()->header("Content-Type", "application/json");
	echo stripslashes(format_json(json_encode($ReturnObject)));
				
	});
	
?>