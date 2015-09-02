<?php
$route = '/api/:api_id/definitions/import/swagger/1.2/';
$app->post($route, function ($api_id)  use ($app){

	$host = $_SERVER['HTTP_HOST'];
	$api_id = prepareIdIn($api_id,$host);

	$ReturnObject = array();

 	$request = $app->request();
 	$param = $request->params();

	$import_date = date('Y-m-d H:i:s');
	$import_id = $param['appid'];
	$api_definition_url = $param['url'];

	$ObjectText = file_get_contents($api_definition_url);
	$ObjectResult = json_decode($ObjectText,true);

	$SwaggerVersion = $ObjectResult['swaggerVersion'];
	$SwaggerBasePath = $ObjectResult['basePath'];
	$SwaggerResourcePath = $ObjectResult['resourcePath'];

	$SwaggerProduces = "";
	if(isset($ObjectResult['produces']))
		{
		$SwaggerProduces = $ObjectResult['produces'];
		}

	$SwaggerAPIs = $ObjectResult['apis'];

	$SwaggerModels = "";
	if(isset($ObjectResult['models']))
		{
		$SwaggerModels = $ObjectResult['models'];
		}

	//echo "Version: " . $SwaggerVersion . "<br />";
	//echo "Base Path: " . $SwaggerBasePath . "<br />";
	//echo "Resource Path: " . $SwaggerResourcePath . "<br />";
	//echo "Produces: " . $SwaggerProduces . "<br />";

	$InsertQuery = "INSERT INTO api_swagger(API_ID,import_date,import_id,host,basePath) VALUES(" . $api_id . ",'" . $import_date . "','" . $import_id . "','" . $SwaggerResourcePath . "','" . $SwaggerBasePath . "')";
	echo $InsertQuery . "<br />";
	mysql_query($InsertQuery) or die('Query failed: ' . mysql_error());
	$api_definition_id = mysql_insert_id();

	foreach($SwaggerAPIs as $APIs)
		{

		$path = $APIs['path'];
		//echo "<br />Path: " . $path . "<br />";

		$operations = $APIs['operations'];
		foreach($operations as $operation)
			{

			$method = $operation['method'];
			$type = $method;
			$operation_summary = $operation['summary'];
			$operation_description = $operation['notes'];
			$operation_operationId = $operation['nickname'];

			$SwaggerPathQuery = "SELECT * FROM api_swagger_paths WHERE API_Swagger_ID = " . $api_definition_id . " AND path = '" . $path . "'  AND pathtype = '" . $type . "'";
			//echo $SwaggerPathQuery . "<br />";
			$SwaggerPathResults = mysql_query($SwaggerPathQuery) or die('Query failed: ' . mysql_error());

			if($SwaggerPathResults && mysql_num_rows($SwaggerPathResults))
				{
				$SwaggerPath = mysql_fetch_assoc($SwaggerPathResults);
				$Swagger_Path_ID = $SwaggerPath['ID'];
				}
			else
				{
				$InsertQuery = "INSERT INTO api_swagger_paths(API_Swagger_ID,path,pathtype,operation_summary,operation_description,operation_operationId) VALUES(" . $api_definition_id . ",'" . $path . "','" . $type . "','" . mysql_real_escape_string($operation_summary) . "','" . mysql_real_escape_string($operation_description) . "','" . mysql_real_escape_string($operation_operationId) . "')";
				//echo $InsertQuery;
				mysql_query($InsertQuery) or die('Query failed: ' . mysql_error());
				$Swagger_Path_ID = mysql_insert_id();
				}

			$parameters = $operation['parameters'];
			$responseMessages = $operation['responseMessages'];

			//echo "method: " . $method . "<br />";
			//echo "summary: " . $operation_summary . "<br />";
			//echo "notes: " . $notes . "<br />";
			//echo "nickname: " . $nickname . "<br />";
			//echo "type: " . $type . "<br />";

			//var_dump($parameters);
			foreach($parameters as $parameter)
				{

				$parameter_id = '';
				if(isset($parameter['id']))
					{
					$parameter_id = $parameter['id'];
					}
				$parameter_name = "";
				if(isset($parameter['name']))
					{
					$parameter_name = $parameter['name'];
					}

				$parameter_description = '';
				if(isset($parameter['description']))
					{
					$parameter_description = $parameter['description'];
					}

				$parameter_required = $parameter['required'];
				if($parameter_required=='')
					{
					$parameter_required = 0;
					}

				$parameter_allowMultiple = 0;
				if(isset($parameter['allowMultiple']))
					{
					$parameter_allowMultiple = $parameter['allowMultiple'];
					}

				$parameter_dataType = "";
				if(isset($parameter['dataType']))
					{
					$parameter_dataType = $parameter['dataType'];
					}

				$parameter_paramType = "";
				if(isset($parameter['paramType']))
					{
					$parameter_paramType = $parameter['paramType'];
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
					//echo $InsertQuery;
					mysql_query($InsertQuery) or die('Query failed: ' . mysql_error());
					}

				}

			foreach($responseMessages as $responsemessage)
				{
				$esponseMessage_code = $responsemessage['code'];
				$esponseMessage_code = $responsemessage['message'];
				//echo "code: " . $esponseMessage_code . "<br />";
				//echo "message: " . $esponseMessage_code . "<br />";
				}


			}
		}

	// Data Model
	foreach($SwaggerModels as $model)
		{

		$model_id = $model['id'];
		//echo "id: " . $model_id . "<br />";
		$model_properties = $model['properties'];

		$SwaggerDefinitionQuery = "SELECT * FROM api_swagger_definitions WHERE API_Swagger_ID = " . $api_definition_id . " AND name = '" . $model_id . "'";
		//echo $SwaggerDefinitionQuery . "<br />";
		$SwaggerDefinitionResults = mysql_query($SwaggerDefinitionQuery) or die('Query failed: ' . mysql_error());
		if($SwaggerDefinitionResults && mysql_num_rows($SwaggerDefinitionResults))
			{
			$SwaggerDefinition = mysql_fetch_assoc($SwaggerDefinitionResults);
			$Swagger_Definition_ID = $SwaggerDefinition['ID'];
			}
		else
			{
			$InsertQuery = "INSERT INTO api_swagger_definitions(API_Swagger_ID,name) VALUES(" . $api_definition_id . ",'" . $model_id . "')";
			//echo $InsertQuery;
			mysql_query($InsertQuery) or die('Query failed: ' . mysql_error());
			$Swagger_Definition_ID = mysql_insert_id();
			}

		foreach($model_properties as $key => $value)
			{
			$property_name = $key;

			//echo $key . " = " . $value;
			//echo "name: " . $name . "<br />";
			//echo "type: " . $type . "<br />";
			//echo "<br />";
			if($property_name!='')
				{
				$property_type = "string";
				if(isset( $_REQUEST['name']))
					{
					$property_type = $value['type'];
					}
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

	$ReturnObject['version'] = "Import of Swagger 1.2 (" . $api_definition_url . ") successful.";

	$app->response()->header("Content-Type", "application/json");
	echo stripslashes(format_json(json_encode($ReturnObject)));

	});
?>
