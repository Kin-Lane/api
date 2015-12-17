<?php
$route = '/api/apisjson/how-big/';
$app->get($route, function ()  use ($app,$contentType,$githuborg,$githubrepo){

	$ReturnObject = array();

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

		$apis_json_results = file_get_contents($apisjson_url);

		$apis_json = json_decode($apis_json_results);
		$oadf_apis = $apis_json->apis;

		$NumberofPaths = 0;
		$NumberofVerbs = 0;
		$NumberofParameters = 0;
		$NumberofResponses = 0;
		$numberofDefinitions = 0;

		foreach($oadf_apis as $apis)
			{
			foreach($apis->properties as $apis_properties)
				{
				$type = $apis_properties->type;
				if($type=="Swagger" || $type=="swagger" || $type=="X-oadf")
					{
					$oadf_url = $apis_properties->url;
					$oadf_json = file_get_contents($oadf_url);
					$apis_path = json_decode($oadf_json,true);

					$ThisPaths = 0;
					$ThisVerbs = 0;
					$ThisParameters = 0;
					$ThisResponses = 0;
					$ThisDefinitions = 0;

					foreach($apis_path['paths'] as $key => $value)
						{
						$ThisPaths++;
						foreach($value as $key2 => $value2)
							{
							$ThisVerbs++;
							if(isset($value2['parameters']))
								{
								foreach($value2['parameters'] as $parameters)
									{
									$ThisParameters++;
									}
								}
								if(isset($value2['responses']))
									{
									foreach($value2['responses'] as $responses)
										{
										$ThisResponses++;
										}
									}
							}
						}

					foreach($apis_path['definitions'] as $definitions)
						{
						$ThisDefinitions++;
						}

					$NumberofPaths = $NumberofPaths + $ThisPaths;
					$NumberofVerbs = $NumberofVerbs + $ThisVerbs;
					$NumberofParameters = $NumberofParameters + $ThisParameters;
					$NumberofResponses = $NumberofResponses + $ThisResponses;
					$numberofDefinitions = $numberofDefinitions + $ThisDefinitions;
					}
				}
			}
		$ReturnObject['paths'] = $NumberofPaths;
		$ReturnObject['verbs'] = $NumberofVerbs;
		$ReturnObject['parameters'] = $NumberofParameters;
		$ReturnObject['responses'] = $NumberofResponses;
		$ReturnObject['definitions'] = $numberofDefinitions;
		$app->response()->header("Content-Type", "application/json");
		echo format_json(json_encode($ReturnObject));
		}
	});
?>
