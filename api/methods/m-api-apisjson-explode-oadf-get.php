<?php
$route = '/api/apisjson/explode/oadf/';
$app->get($route, function ()  use ($app,$contentType,$githuborg,$githubrepo){

	$Explode = array();

	$ReturnObject = array();
	//$ReturnObject['contentType'] = $contentType;

	if($contentType == 'application/apis+json')
		{
		$app->response()->header("Content-Type", "application/json");

		$apis_json_url = "http://" . $githuborg . ".github.io/" . $githubrepo . "/apis.json";
		$apis_json = file_get_contents($apis_json_url);
		echo stripslashes(format_json($apis_json));
		}
	else
		{
		if(isset($_REQUEST['apisjson_url'])){ $apisjson_url = $_REQUEST['apisjson_url']; } else { $apisjson_url = '';}

		echo $apisjson_url . "<br />";
		$apis_json_results = file_get_contents($apisjson_url);

		//var_dump($apis_json);

		$apis_json = json_decode($apis_json_results);

		$oadf_name = "";
		if(isset($apis_json->name))
			{
			$oadf_name = $apis_json->name;
			}
		if(isset($apis_json->description))
			{
			$oadf_description = $apis_json->description;
			}
		if(isset($apis_json->image))
			{
			$oadf_image = $apis_json->image;
			}
		if(isset($apis_json->tags))
			{
			$oadf_tags = $apis_json->tags;
			}
		if(isset($apis_json->created))
			{
			$oadf_created = $apis_json->created;
			}
		if(isset($apis_json->modified))
			{
			$oadf_modified = $apis_json->modified;
			}
		if(isset($apis_json->url))
			{
			$oadf_url = $apis_json->url;
			}
		if(isset($apis_json->specificationVersion))
			{
			$oadf_specificationVersion = $apis_json->specificationVersion;
			}

		if(isset($apis_json->apis))
			{
			$oadf_apis = $apis_json->apis;

			foreach($oadf_apis as $apis)
				{
				// Begin each API
				foreach($apis->properties as $apis_properties)
					{
					// Begin Each Property
					$type = $apis_properties->type;

					//echo $type . "<br />";

					if($type=="Swagger")
						{

						$oadf_url = $apis_properties->url;

						//$oadf_url = "http://theapistack.com/data/twitter/twitter-api-swagger.json";
						echo "pulling: " . $oadf_url . "<br />";
						$oadf_json = file_get_contents($oadf_url);
						$apis_path = json_decode($oadf_json,true);

						$group = "";
						$first = 0;

						// Traverse Each Path
						foreach($apis_path['paths'] as $key => $value)
							{
							echo "key: " . $key . "<br />";
							foreach($value as $key2 => $value2)
								{

								$summary = $value2['summary'];
								echo $summary . "<br />";
								$description = $value2['description'];

								$methodArray = explode("/",$summary);
								var_dump($methodArray);
								$path = 0;

								$resource = $methodArray[0];
								if($resource=='/')
									{
									$resource = $methodArray[1];
									}

								echo $group . " != " . $resource . "<br />";

								if($group != $methodArray[0])
									{

									$Break = $methodArray[0];

									$ThisPaths = new stdClass;
									$ThisDefinitions = new stdClass;

									$Explode[$Break] = array();
									$Explode[$Break]['definitions'] = array();

									$LetterOADF = array();

									$LetterOADF['swagger'] = $apis_path['swagger'];

									$LetterOADFInfo['title'] = $apis_path['info']['title'];
									$LetterOADFInfo['description'] = $apis_path['info']['description'];
									$LetterOADFInfo['termsOfService'] = $apis_path['info']['termsOfService'];
									$LetterOADFInfo['version'] = $apis_path['info']['version'];

									$LetterOADF['info'] = $apis_path['info'];

									$LetterOADF['host'] = $apis_path['host'];

									$basePath = $apis_path['basePath'];
									if(substr($basePath,strlen($basePath)-1,1) == "/")
										{
										$basePath = substr($basePath,0,strlen($basePath)-1);
										}
									$LetterOADF['basePath'] = $basePath;

									$LetterOADF['schemes'] = $apis_path['schemes'];

									$LetterOADF['produces'] = $apis_path['produces'];

									$LetterOADF['produces'] = array();

									$LetterOADF['paths'] = new stdClass;

									$Explode[$Break] = $LetterOADF;

									$group = $methodArray[0];

									}
								else
									{

									$Break = array();

									if(substr($key,0,1) != "/")
										{
										$key = "/" . $key;
										}

									$ThisPaths->$key = new stdClass;
									$ThisPaths->$key = $value;

									if(isset($value2['responses']))
										{
										foreach($value2['responses'] as $response)
											{
											if(isset($response['schema']))
												{
												foreach($response['schema'] as $schema)
													{
													if(is_array($schema))
														{
														$refDefinitions = $schema[chr(36) . "ref"];
														$refDefinitions = str_replace("#/definitions/","",$refDefinitions);
														if($apis_path['definitions'][$refDefinitions])
															{
															$ThisDefinitions->$refDefinitions = new stdClass;
															$ThisDefinitions->$refDefinitions = $apis_path['definitions'][$refDefinitions];
															}
														}
													}
												}
											}
										}
									}

								$Explode[$Break]['paths'] = new stdClass;
								$Explode[$Break]['paths'] = $ThisPaths;
								$Explode[$Break]['definitions'] = new stdClass;
								$Explode[$Break]['definitions'] = $ThisDefinitions;

								}
							}

						}
					// End Each Property
					}
				// End Each API
				}
			}

		$app->response()->header("Content-Type", "application/json");
		echo stripslashes(format_json(json_encode($Explode)));

		}
	});

?>
