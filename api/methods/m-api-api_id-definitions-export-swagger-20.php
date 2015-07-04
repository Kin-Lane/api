<?php
$route = '/api/:api_id/definitions/export/swagger/2.0/';
$app->get($route, function ($api_id)  use ($app,$awsAccessKey,$awsSecretKey,$awsSiteBucket,$awsRootURL,$guser,$gpass){

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

	$NameSlug = "";
	$CompanyQuery = "SELECT c.Company_ID, c.Name FROM company c JOIN company_api_pivot cap ON c.Company_ID = cap.Company_ID WHERE cap.API_ID = " . $api_id;
	//echo $CompanyQuery . "<br />";
	$CompanyResults = mysql_query($CompanyQuery) or die('Query failed: ' . mysql_error());
	if($CompanyResults && mysql_num_rows($CompanyResults))
		{
		$Company = mysql_fetch_assoc($CompanyResults);
		$Company_ID = $Company['Company_ID'];
		$Company_Name = $Company['Name'];
		$Company_Slug = PrepareFileName($Company_Name);
		}

	$TagArray = array();
	$TagQuery = "SELECT t.Tag FROM company c JOIN company_tag_pivot ctp ON c.Company_ID = ctp.Company_ID JOIN tags t ON ctp.Tag_ID = t.Tag_ID WHERE c.Company_ID = " . $Company_ID;
	//echo $TagQuery . "<br />";
	$TagResults = mysql_query($TagQuery) or die('Query failed: ' . mysql_error());

	if($TagResults && mysql_num_rows($TagResults))
		{
		while ($Tag = mysql_fetch_assoc($TagResults))
			{
			$ThisTag = $Tag['Tag'];
			array_push($TagArray, $ThisTag);
			}
		}

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

		$Swagger = array();
		$Swagger['swagger'] = "2.0";
		$Swagger['info'] = array();

		$Info = array();
		$Info['title'] = $info_title;
		$Info['description'] = $info_description;
		$Info['termsOfService'] = $info_termsOfService;

		$Contact = array();
		$Contact['name'] = $info_contact_name;
		$Contact['url'] = $info_contact_url;
		$Contact['email'] = $info_contact_email;
		if($info_contact_name!='')
			{
			$Info['contact'] = array();
			$Info['contact'] = $Contact;
			}

		$License = array();
		$License['name'] = $info_license_name;
		$License['url'] = $info_license_url;

		if($info_license_name!='')
			{
			$Info['license'] = array();
			$Info['license'] = $License;
			}

		$Info['version'] = $info_version;

		$Swagger['info'] = $Info;

		if($host!='')
			{
			$Swagger['host'] = $host;
			}
		if($basePath!='')
			{
			$Swagger['basePath'] = $basePath;
			}

		$SchemesArray = array('http');
		$Swagger['schemes'] = $SchemesArray;

		$ProducesArray = array('application/json');
		$Swagger['produces'] = $ProducesArray;

		$ProducesArray = array('application/json');
		$Swagger['consumes'] = $ProducesArray;

		$SwaggerPath = new stdClass();

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
				$operation_description = str_replace(chr(34),"",$operation_description);
				$operation_description = str_replace(chr(39),"",$operation_description);
				$operation_operationId = $SwaggerPathRow['operation_operationId'];
				$schemas = $SwaggerPathRow['pathschemas'];
				$tags = $SwaggerPathRow['tags'];

				$SwaggerPath->$path->$type = new stdClass();

				$SwaggerPathTypeDetail = array();
				$SwaggerPathTypeDetail['summary'] = $operation_summary;
				$SwaggerPathTypeDetail['description'] = $operation_description;
				$SwaggerPathTypeDetail['operationId'] = $operation_operationId;

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
						$field_description = str_replace(chr(34),"",$field_description);
						$field_description = str_replace(chr(39),"",$field_description);
						$field_required = $SwaggerPathField['required'];
						$field_type = $SwaggerPathField['fieldtype'];
						$field_format = $SwaggerPathField['fieldformat'];
						$field_default = $SwaggerPathField['fielddefault'];

						$SwaggerPathTypeField = array();
						$SwaggerPathTypeField['in'] = $field_in;
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
							}
						if($field_format!='' && $field_in != 'body')
							{
							$SwaggerPathTypeField['format'] = $field_format;
							}
						if($field_default!='' && $field_in != 'body')
							{
							$SwaggerPathTypeField['default'] = $field_default;
							}

						if($field_in == 'body')
							{

							$SwaggerPathTypeField['schema'] = array();

							$R = array();
							$R['$ref'] = "#/definitions/holder";

							$SwaggerPathTypeField['schema'] = $R;


							unset($R);
							unset($SwaggerPathResponseArray);
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
						$response_description = str_replace(chr(34),"",$response_description);
						$response_description = str_replace(chr(39),"",$response_description);

						$response_schema = "";
						if(isset($SwaggerPathResponse['responseschema']))
							{
							$response_schema = $SwaggerPathResponse['responseschema'];
							}

						$SwaggerPathResponseArray[$response_name] = array();

						$R = array();
						$R['description'] = $response_description;

						if($response_schema!='')
							{

							$R['schema'] = array();

							$S = array();
							$S['type'] = "array";

							$I = array();
							$I['$ref'] = "#/definitions/" . $response_schema;

							$S['items'] = $I;

							$R['schema'] =  $S;
							}

						$SwaggerPathResponseArray[$response_name] = $R;

						array_push($ResponsesArray, $SwaggerPathResponseArray);

						unset($R);
						unset($S);
						}
					}
				else
					{
					$response_name = "200";
					$response_description = "OK";

					$SwaggerPathResponseArray[$response_name] = array();

					$R = array();
					$R['description'] = $response_description;

					$SwaggerPathResponseArray[$response_name] = $R;

					array_push($ResponsesArray, $SwaggerPathResponseArray);

					unset($R);
					unset($S);
					unset($SwaggerPathResponseArray);
					}

				foreach ($ResponsesArray as $key => $value)
					{
					$SwaggerPathTypeDetail['responses'] = $value;
					}

				$SwaggerPathTypeDetail['operationId'] = $operation_operationId;

				$TagArray = explode(",",$tags);
				if($TagArray)
					{
					$SwaggerPathTypeDetail['tags'] = array();
					$SwaggerPathTypeDetail['tags'] = $TagArray;
					}

				$SwaggerPathTypeDetail['security'] = array();

				$SwaggerSecurityDefinitionQuery = "SELECT aspsd.ID as ID, assd.Name as name FROM api_swagger_security_definitions assd JOIN api_swagger_path_security_definition aspsd ON assd.ID = aspsd.api_swagger_security_definition_id WHERE api_swagger_path_id = " . $Swagger_Path_ID;
				//echo $SwaggerSecurityDefinitionQuery . "<br />";
				$SwaggerSecurityDefinitionResults = mysql_query($SwaggerSecurityDefinitionQuery) or die('Query failed: ' . mysql_error());

				if($SwaggerSecurityDefinitionResults && mysql_num_rows($SwaggerSecurityDefinitionResults))
					{
					while ($SwaggerSecurityDefinition = mysql_fetch_assoc($SwaggerSecurityDefinitionResults))
						{
						$Security_Path_Security_Definition_ID = $SwaggerSecurityDefinition['ID'];
						$security_definition_name = $SwaggerSecurityDefinition['name'];

						$S = array();
						$S[$security_definition_name] = array();

						array_push($SwaggerPathTypeDetail['security'], $S);

						}
					}

				$SwaggerPath->$path->$type = $SwaggerPathTypeDetail;
				unset($SwaggerPathTypeDetail);

				}
			}

		$Swagger['paths'] = $SwaggerPath;

		$SecurityDefinition = array();

		$SwaggerDefinitionQuery = "SELECT * FROM api_swagger_security_definitions WHERE API_Swagger_ID = " . $API_Swagger_ID;
		//echo $SwaggerPathFieldQuery . "<br />";
		$SwaggerDefinitionResults = mysql_query($SwaggerDefinitionQuery) or die('Query failed: ' . mysql_error());

		if($SwaggerDefinitionResults && mysql_num_rows($SwaggerDefinitionResults))
			{
			while ($SwaggerDefinition = mysql_fetch_assoc($SwaggerDefinitionResults))
				{
				$Definition_ID = $SwaggerDefinition['ID'];
				$security_definition_name = $SwaggerDefinition['name'];
				$security_definition_type = $SwaggerDefinition['definition_type'];
				$security_definition_description = $SwaggerDefinition['description'];
				$security_definition_in = $SwaggerDefinition['definition_in'];

				$SecurityDefinitionArray[$security_definition_name] = array();
				$SecurityDefinitionPropertiesArray['type'] = $security_definition_type;
				$SecurityDefinitionPropertiesArray['name'] = $security_definition_name;
				$SecurityDefinitionPropertiesArray['in'] = $security_definition_in;

				array_push($SecurityDefinition, $SecurityDefinitionPropertiesArray);
			}
		}

		if(count($SecurityDefinition)>0)
			{
			$Swagger['securityDefinitions'] = new stdClass();
			foreach ($SecurityDefinition as $key => $value)
				{
				//echo $key . "<br />";
				//var_dump($value);
				$name = $value['name'];
				$Swagger['securityDefinitions']->$name = $value;
				}
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
				$DefinitionPropertiesArray['properties'] = array();
				$DefinitionPropertyArray = array();

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
						$property_description = str_replace(chr(34),"",$property_description);
						$property_description = str_replace(chr(39),"",$property_description);
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
			$Swagger['definitions'] = new stdClass();
			foreach ($DefinitionArray as $key => $value)
				{
				//echo $key . " = " . $value;
				$Swagger['definitions']->$key = $value;
				}
			}

		$Swagger['definitions'] = $DefinitionArray;

		}

	$Swagger_JSON = stripslashes(format_json(json_encode($Swagger)));
	//echo $Swagger_JSON . "<br />";
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

	$ReturnJSON = stripslashes(format_json(json_encode($ReturnObject)));
	//echo $ReturnJSON;
	//echo "<hr />";
	// Send to API Stack

	$swagger_store_file = "data/" . $Company_Slug . "/" . $API_Name_Slug . "-swagger.json";
	$swagger_url = "http://theapistack.com/" . $swagger_store_file;
	//echo $swagger_url;

	$CompanyQuery = "SELECT * FROM api_url WHERE API_ID = " . $api_id . " AND Type = 'Swagger'";
	//echo $CompanyQuery . "<br />";
	$CompanyResults = mysql_query($CompanyQuery) or die('Query failed: ' . mysql_error());
	if($CompanyResults && mysql_num_rows($CompanyResults))
		{
		$Company = mysql_fetch_assoc($CompanyResults);
		}
	else
		{
		$CompanyQuery = "INSERT INTO api_url(API_ID,URL,TYPE) VALUES(" . $api_id . ",'" . $swagger_url . "','Swagger')";
		//echo $CompanyQuery . "<br />";
		$CompanyResults = mysql_query($CompanyQuery) or die('Query failed: ' . mysql_error());
		}

	$owner = "api-stack";
	$project_github_repo = "api-stack";
	$ref = "gh-pages";
	try
		{
		$GitHubClient = new GitHubClient();
		$GitHubClient->setCredentials($guser, $gpass);

		$CheckFile = $GitHubClient->repos->contents->getContents($owner, $project_github_repo, $ref, $swagger_store_file);
		$name = $CheckFile->getname();
		$content = base64_decode($CheckFile->getcontent());
		$sha = $CheckFile->getsha();

		$message = "Updating " . $swagger_store_file . " via Laneworks Publish";
		$content = base64_encode($Swagger_JSON);
		$updateFile = $GitHubClient->repos->contents->updateFile($owner, $project_github_repo, $swagger_store_file, $message, $content, $sha, $ref);
		}
	catch (Exception $e)
		{
		$GitHubClient = new GitHubClient();
		$GitHubClient->setCredentials($guser, $gpass);

		$message = "Adding " . $swagger_store_file . " via Laneworks Publish";
		$content = base64_encode($Swagger_JSON);
		$updateFile = $GitHubClient->repos->contents->createFile($owner, $project_github_repo, $swagger_store_file, $message, $content, $ref);
		}

	$ref = "master";
	try
		{
		$GitHubClient = new GitHubClient();
		$GitHubClient->setCredentials($guser, $gpass);

		$CheckFile = $GitHubClient->repos->contents->getContents($owner, $project_github_repo, $ref, $swagger_store_file);
		$name = $CheckFile->getname();
		$content = base64_decode($CheckFile->getcontent());
		$sha = $CheckFile->getsha();

		$message = "Updating " . $swagger_store_file . " via Laneworks Publish";
		$content = base64_encode($Swagger_JSON);
		$updateFile = $GitHubClient->repos->contents->updateFile($owner, $project_github_repo, $swagger_store_file, $message, $content, $sha, $ref);
		}
	catch (Exception $e)
		{
		$GitHubClient = new GitHubClient();
		$GitHubClient->setCredentials($guser, $gpass);

		$message = "Adding " . $swagger_store_file . " via Laneworks Publish";
		$content = base64_encode($Swagger_JSON);
		$updateFile = $GitHubClient->repos->contents->createFile($owner, $project_github_repo, $swagger_store_file, $message, $content, $ref);
		}

	unlink($local_file);

	$app->response()->header("Content-Type", "application/json");
	echo stripslashes(format_json(json_encode($ReturnObject)));

	});
?>
