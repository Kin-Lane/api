<?php
$route = '/api/:api_id/definitions/export/apisjson/.14/';	;
$app->get($route, function ($api_id)  use ($app){
	
	$host = $_SERVER['HTTP_HOST'];		
	$api_id = prepareIdIn($api_id,$host);	
	
	$ReturnObject = array();
		
 	$request = $app->request(); 
 	$param = $request->params();
	
	if(isset($param['filterobject'])){ $filterobject = $param['filterobject']; } else { $filterobject = '';}
	
	$Company_Count = 0;
	$Write_APIS_JSON_File = 0;
	
	$GetCompanyQuery = "SELECT DISTINCT c.Company_ID,c.Name,c.Details,c.Twitter_Bio,c.Bio FROM company c JOIN company_api_pivot cap ON c.Company_ID = cap.Company_ID WHERE cap.API_ID = " . $api_id . " ORDER BY c.Name ASC;";
	//echo $GetCompanyQuery . "<br />";
	$GetCompanyResult = mysql_query($GetCompanyQuery) or die('Query failed: ' . mysql_error());			
	if($GetCompanyResult && mysql_num_rows($GetCompanyResult))
		{	
		while ($Company = mysql_fetch_assoc($GetCompanyResult))
			{
			$Company_ID = $Company['Company_ID'];
			
			$Company_Name = $Company['Name'];
			echo $Company_Name . "<br />";							
			
			$Company_Name_Slug = PrepareFileName($Company_Name);
			
			$API_JSON_URL = "http://theapistack.com/" . $Company_Name_Slug . "/apis.json";
			
			$Body = $Company['Details'];					
									
			$Body = str_replace(chr(34),"",$Body);
			$Body = str_replace(chr(39),"",$Body);
			$Body = strip_tags($Body);
			$Body = mysql_real_escape_string($Body);
			
			// Add Company As Include in Master APIs.json
			$APIJSON_Include = array();
			$APIJSON_Include['name'] = $Company_Name;
			$APIJSON_Include['url'] = $API_JSON_URL;		
			
			// Logo Image
			$Logo_Image_Width = 0;
			$Company_Image_ID = 0;					
			$LogoImageQuery = "SELECT Company_Image_ID, Image_Path,Width FROM company_image WHERE Type = 'logo' AND Company_ID = " . $Company_ID . " ORDER BY Company_Image_ID DESC LIMIT 1";
			//echo $LogoImageQuery . "<br />";
			$LogoImageResult = mysql_query($LogoImageQuery) or die('Query failed: ' . mysql_error());						
			while ($LogoImage = mysql_fetch_assoc($LogoImageResult))
				{
				$Company_Image_ID = $LogoImage['Company_Image_ID'];						
				$Logo_Image_Path = $LogoImage['Image_Path'];
				$Logo_Image_Width = $LogoImage['Width'];		
				}
												
			// Begin Individual APIs.json
			$APIJSON = array();
			$APIJSON['name'] = trim($Company_Name);
			$APIJSON['description'] = trim($Body);
			$APIJSON['image'] = trim($Logo_Image_Path);
			
			// Maange the API.json Tags
			$Tags = array();
			$Tag =  array('api');
			array_push($Tags, $Tag);
				
			$Tag =  array('application programming interfaces');
			array_push($Tags, $Tag);
			
			$Tags = array();	
			$TagQuery = "SELECT DISTINCT t.Tag FROM tags t JOIN company_tag_pivot ctp ON t.Tag_ID = ctp.Tag_ID WHERE ctp.Company_ID = " . $Company_ID . " AND t.Tag NOT LIKE '%-Stack' ORDER BY t.Tag";
			//echo $TagQuery;
			$TagResult = mysql_query($TagQuery) or die('Query failed: ' . mysql_error());
			$rowcount = 1;
			while ($ThisTag = mysql_fetch_assoc($TagResult))
				{
				$Tag = strtolower($ThisTag['Tag']);
				array_push($Tags, $Tag);
				}					
																	
			$APIJSON['tags'] = $Tags;
							
			$APIJSON['created'] = date('Y-m-d');
			$APIJSON['modified'] = date('Y-m-d');			
	
			$APIJSON['url'] = $API_JSON_URL;
			$APIJSON['specificationVersion'] = "0.14";	
			
			$APIJSON['apis'] = array();	

			// Begin APIs						
			$APIQuery = "SELECT * FROM api WHERE Company_ID = " . $Company_ID . " ORDER BY Name";
			//echo $TagQuery;
			$APIResult = mysql_query($APIQuery) or die('Query failed: ' . mysql_error());
			$rowcount = 1;
			
			if($APIResult && mysql_num_rows($APIResult))
				{					
				while ($API = mysql_fetch_assoc($APIResult))
					{
					$api_id = $API['API_ID'];
					$API_Name = $API['Name'];
					$API_About = $API['About'];		
							
					$API_About = str_replace(chr(34),"",$API_About);
					$API_About = str_replace(chr(39),"",$API_About);
					$API_About = strip_tags($API_About);
					$API_About = mysql_real_escape_string($API_About);											
					
					$API = array();
					$API['name'] = $API_Name;
					$API['description'] = $API_About;
					$API['image'] = trim($Logo_Image_Path);
					
					// Website	
					$API_Website_URL = "";
					$query = "SELECT URL FROM api_url WHERE API_ID = " . $api_id . " AND Type = 'Website'";
					$linkResult = mysql_query($query) or die('Query failed: ' . mysql_error());
					if($linkResult && mysql_num_rows($linkResult))
						{	
						while ($link = mysql_fetch_assoc($linkResult))
							{
							$API_Website_URL = $link['URL'];
							}
						}
					
					$API['humanURL'] = trim($API_Website_URL);
										
					// Base URL
					$API_Base_URL = "";
					$query = "SELECT URL FROM api_url WHERE API_ID = " . $api_id . " AND Type = 'BaseURL'";
					$linkResult = mysql_query($query) or die('Query failed: ' . mysql_error());
					if($linkResult && mysql_num_rows($linkResult))
						{	
						while ($link = mysql_fetch_assoc($linkResult))
							{
							$API_Base_URL = $link['URL'];
							}
						}																				
					
					if($API_Base_URL!='')
						{
						$API['baseURL'] = trim($API_Base_URL);
						}				
					else				
						{
						$API['baseURL'] = trim($API_Website_URL);
						}

					// Begin API Tags
					$API_Tags = array();	
					$TagQuery = "SELECT DISTINCT t.Tag FROM tags t JOIN api_tag_pivot atp ON t.Tag_ID = atp.Tag_ID WHERE atp.API_ID = " . $api_id . " AND t.Tag NOT LIKE '%-Stack' ORDER BY t.Tag";
					//echo $TagQuery;
					$TagResult = mysql_query($TagQuery) or die('Query failed: ' . mysql_error());
					$rowcount = 1;
					while ($ThisTag = mysql_fetch_assoc($TagResult))
						{
						$Tag = strtolower($ThisTag['Tag']);
						array_push($API_Tags, $Tag);
						}										
					$API['tags'] = $API_Tags;
					// End API Tags					
	
					$API['properties'] = array();
					
					$CompanyURLQuery = "SELECT * FROM company_url WHERE Company_ID = " . $Company_ID . " ORDER BY Name, Type";
					//echo $CompanyURLQuery . "<br />";
					$CompanyURLResult = mysql_query($CompanyURLQuery) or die('Query failed: ' . mysql_error());					
							
					while ($CompanyURL = mysql_fetch_assoc($CompanyURLResult))
						{
						$Company_URL_ID = $CompanyURL['Company_URL_ID'];
						
						$API_URL = $CompanyURL['URL'];
						$API_URL_Name = $CompanyURL['Name'];
						$API_URL_Type = $CompanyURL['Type'];
						
						$API_Building_Block_ID = $CompanyURL['Building_Block_ID'];
						//echo "API Building Block ID: " . $API_Building_Block_ID . "<br />";
						//echo "API URL Name: " . $API_URL_Name . "<br />";
						
						$API_Building_Block_Name = "";
						$API_Building_Block_Description = "";
						$API_Building_Block_Icon = "";
						
						if($API_Building_Block_ID>0)
							{
								
							$Building_Block_Query = "SELECT Building_Block_ID, bb.Name AS Building_Block_Name, bb.About, bbc.Name AS Building_Block_Category_Name, bbc.Type as Type FROM building_block bb JOIN building_block_category bbc ON bb.Building_Block_Category_ID = bbc.BuildingBlockCategory_ID WHERE Building_Block_ID = " . $API_Building_Block_ID;
							//echo $Building_Block_Query . "<br />";	
							$Building_Block_Result = mysql_query($Building_Block_Query) or die('Query failed: ' . mysql_error());	
							if($Building_Block_Result && mysql_num_rows($Building_Block_Result))
								{
								$HaveBuildingBlock = 1;
								$Building_Block = mysql_fetch_assoc($Building_Block_Result);

								$Building_Block_Image_Query = "SELECT Image_Name,Image_Path FROM building_block_image WHERE Image_Path <> '' AND Building_Block_ID = " . $API_Building_Block_ID . " ORDER BY Building_Block_Image_ID DESC";
								$Building_Block_Image_Result = mysql_query($Building_Block_Image_Query) or die('Query failed: ' . mysql_error());
								while ($Building_Block_Image = mysql_fetch_assoc($Building_Block_Image_Result))
									{
									$API_Building_Block_Icon = $Building_Block_Image['Image_Path'];					
									}								
										
								$API_Building_Block_Name = $Building_Block['Building_Block_Name'];
								//echo "Building Block Name: " . $API_Building_Block_Name . "<br />";
								$API_Building_Block_Description = $Building_Block['About'];							
										
								}	

							$API_URL_Type_Slug = PrepareFileName($API_Building_Block_Name);

							$Link = array();
							$Link['type'] = "X-" . $API_URL_Type_Slug;
							$Link['url'] = trim($API_URL);
							array_push($API['properties'], $Link);
				
							}
						}	
						
					$CompanyURLQuery = "SELECT API_URL_ID,URL FROM api_url WHERE API_ID = " . $api_id . " AND Type = 'Swagger'";
					//echo $CompanyURLQuery . "<br />";
					$CompanyURLResult = mysql_query($CompanyURLQuery) or die('Query failed: ' . mysql_error());					
							
					while ($CompanyURL = mysql_fetch_assoc($CompanyURLResult))
						{
						$API_URL_ID = $CompanyURL['API_URL_ID'];							
						$API_URL = $CompanyURL['URL'];
			
						$Link = array();
						$Link['type'] = "Swagger";
						$Link['url'] = trim($API_URL);
						array_push($API['properties'], $Link);
						}																			
								
					// Twitter	
					$Company_API_Twitter = "";
					$query = "SELECT Company_URL_ID,Type,URL FROM company_url WHERE Company_ID = " . $Company_ID . " AND Type = 'Twitter'";
					$linkResult = mysql_query($query) or die('Query failed: ' . mysql_error());
					if($linkResult && mysql_num_rows($linkResult))
						{	
						while ($link = mysql_fetch_assoc($linkResult))
							{
							$Company_API_Twitter = $link['URL'];
							}
						}
						
					// Email	
					$Company_API_Email = "";
					$query = "SELECT Company_URL_ID,Type,URL FROM company_url WHERE Company_ID = " . $Company_ID . " AND Type = 'Email'";
					$linkResult = mysql_query($query) or die('Query failed: ' . mysql_error());
					if($linkResult && mysql_num_rows($linkResult))
						{	
						while ($link = mysql_fetch_assoc($linkResult))
							{
							$Company_API_Email = $link['URL'];
							}
						}						
														
										
					$API['contact'] = array();		
					$Contact = array();
					$Contact['FN'] = $API_Name;
					if($Company_API_Email!='')
						{
						$Contact['email'] = $Company_API_Email;
						}						
					if($Company_API_Twitter!='')
						{
						$Contact['X-twitter'] = $Company_API_Twitter;
						}				
					array_push($API['contact'], $Contact);
					
					// End Each API
					// Append
					array_push($APIJSON['apis'], $API);	

					}

				$APIJSON['maintainers'] = array();
		
				$Maintainer = array();
				$Maintainer['FN'] = "Kin";
				$Maintainer['X-twitter'] = "apievangelist";
				$Maintainer['email'] = "kin@email.com";
		
				array_push($APIJSON['maintainers'], $Maintainer);	
				
				$ReturnEachAPIJSON = stripslashes(format_json(json_encode($APIJSON)));

				$API_JSON_Store_File = "data/" . $Company_Name_Slug . "/apis.json";
				
				$Public_APIs_JSON = $Project_Subdomain . "/" . $API_JSON_Store_File;

				$GitHubClient = new GitHubClient();
				$GitHubClient->setCredentials($GUserName, $GUserPass);
				
				$owner = 'kinlane';				
				$ref = "gh-pages";
				
				try
					{
			
					$CheckFile = $GitHubClient->repos->contents->getContents($owner, $Project_Github_Repo, $ref, $API_JSON_Store_File);
					
					$name = $CheckFile->getname();
					$content = base64_decode($CheckFile->getcontent());
					$sha = $CheckFile->getsha();
					
					$message = "Updating " . $API_JSON_Store_File . " via Laneworks Publish";
					$content = base64_encode($ReturnEachAPIJSON);
					
					echo "Message: " . $message . "<br />";
		
					$updateFile = $GitHubClient->repos->contents->updateFile($owner, $Project_Github_Repo, $API_JSON_Store_File, $message, $content, $sha, $ref);
					//var_dump($updateFile);				
					}
				catch (Exception $e)
					{					

					$message = "Adding " . $API_JSON_Store_File . " via Laneworks Publish";
					$content = base64_encode($ReturnEachAPIJSON);
					
					echo "Message: " . $message . "<br />";
					
					//echo $Data_Store_File . "<br />";
		
					$updateFile = $GitHubClient->repos->contents->createFile($owner, $Project_Github_Repo, $API_JSON_Store_File, $message, $content, $ref);
					//var_dump($updateFile);						
					
					}	
					
				$UpdateQuery = "UPDATE company SET Stack_Update = 1 WHERE Company_ID = " . $Company_ID;
				$UpdateResult = mysql_query($UpdateQuery) or die('Query failed: ' . mysql_error());																					
				
				$LinkQuery = "SELECT * FROM company_url WHERE Company_ID = " . $Company_ID . " AND URL = '" . $Public_APIs_JSON . "' AND Type = 'APIs.json'";
				//echo $LinkQuery . "<br />";
				$LinkResult = mysql_query($LinkQuery) or die('Query failed: ' . mysql_error());
				
				if($LinkResult && mysql_num_rows($LinkResult))
					{	
					$Link = mysql_fetch_assoc($LinkResult);	
					}
				else 
					{
					$query = "INSERT INTO company_url(Company_ID,URL,Type) VALUES(" . mysql_real_escape_string($Company_ID) . ",'" . mysql_real_escape_string($Public_APIs_JSON) . "','APIs.json')";
					//echo $query . "<br />";
					mysql_query($query) or die('Query failed: ' . mysql_error());			
					}					
				
				}	
			else 
				{

				// Website	
				$Website_URL = "";
				$query = "SELECT Company_URL_ID,Type,URL FROM company_url WHERE Company_ID = " . $Company_ID . " AND Type = 'Website'";
				$linkResult = mysql_query($query) or die('Query failed: ' . mysql_error());
				if($linkResult && mysql_num_rows($linkResult))
					{	
					while ($link = mysql_fetch_assoc($linkResult))
						{
						$Website_URL = $link['URL'];
						}
					}
					
				// Email	
				$Email_Address = "";
				$query = "SELECT Company_URL_ID,Type,URL FROM company_url WHERE Company_ID = " . $Company_ID . " AND Type = 'Email'";
				$linkResult = mysql_query($query) or die('Query failed: ' . mysql_error());
				if($linkResult && mysql_num_rows($linkResult))
					{	
					while ($link = mysql_fetch_assoc($linkResult))
						{
						$Email_Address = $link['URL'];
						}
					}							
					
				// Twitter	
				$Twitter_URL = "";
				$query = "SELECT Company_URL_ID,Type,URL FROM company_url WHERE Company_ID = " . $Company_ID . " AND Type = 'Twitter'";
				$linkResult = mysql_query($query) or die('Query failed: ' . mysql_error());
				if($linkResult && mysql_num_rows($linkResult))
					{	
					while ($link = mysql_fetch_assoc($linkResult))
						{
						$Twitter_URL = $link['URL'];
						}
					}	
					
				// Blog
				$Blog_URL = "";	
				$query = "SELECT Company_URL_ID,Type,URL FROM company_url WHERE Company_ID = " . $Company_ID . " AND Type = 'Blog'";
				$linkResult = mysql_query($query) or die('Query failed: ' . mysql_error());
				if($linkResult && mysql_num_rows($linkResult))
					{	
					while ($link = mysql_fetch_assoc($linkResult))
						{
						$Blog_URL = $link['URL'];
						}
					}	
		
				// Blog RSS	
				$Blog_RSS_URL = "";
				$query = "SELECT Company_URL_ID,Type,URL FROM company_url WHERE Company_ID = " . $Company_ID . " AND Type = 'Blog RSS'";
				$linkResult = mysql_query($query) or die('Query failed: ' . mysql_error());
				if($linkResult && mysql_num_rows($linkResult))
					{	
					while ($link = mysql_fetch_assoc($linkResult))
						{
						$Blog_RSS_URL = $link['URL'];
						}
					}
				$Blog_RSS_URL = str_replace("view-source:","",$Blog_RSS_URL);
				$Blog_RSS_URL = str_replace("feed://","http://",$Blog_RSS_URL);		
					
					
				// Blog RSS	
				$Github_URL = "";
				$query = "SELECT Company_URL_ID,Type,URL FROM company_url WHERE Company_ID = " . $Company_ID . " AND Type = 'Github'";
				$linkResult = mysql_query($query) or die('Query failed: ' . mysql_error());
				if($linkResult && mysql_num_rows($linkResult))
					{	
					while ($link = mysql_fetch_assoc($linkResult))
						{
						$Github_URL = $link['URL'];
						}
					}
					
				// Base URL
				$Base_URL = "";
				$query = "SELECT Company_URL_ID,Type,URL FROM company_url WHERE Company_ID = " . $Company_ID . " AND Type = 'BaseURL'";
				$linkResult = mysql_query($query) or die('Query failed: ' . mysql_error());
				if($linkResult && mysql_num_rows($linkResult))
					{	
					while ($link = mysql_fetch_assoc($linkResult))
						{
						$Base_URL = $link['URL'];
						}
					}										
				
				// Base URL
				$Developer_URL = "";
				$query = "SELECT Company_URL_ID,Type,URL FROM company_url WHERE Company_ID = " . $Company_ID . " AND Type = 'Developer'";
				$linkResult = mysql_query($query) or die('Query failed: ' . mysql_error());
				if($linkResult && mysql_num_rows($linkResult))
					{	
					while ($link = mysql_fetch_assoc($linkResult))
						{
						$Developer_URL = $link['URL'];
						}
					}										
								
				
				$Tags = array();	
				$TagQuery = "SELECT DISTINCT t.Tag FROM tags t JOIN company_tag_pivot ctp ON t.Tag_ID = ctp.Tag_ID WHERE ctp.Company_ID = " . $Company_ID . " AND t.Tag NOT LIKE '%-Stack' ORDER BY t.Tag";
				//echo $TagQuery;
				$TagResult = mysql_query($TagQuery) or die('Query failed: ' . mysql_error());
				$rowcount = 1;
				while ($ThisTag = mysql_fetch_assoc($TagResult))
					{
					$Tag = strtolower($ThisTag['Tag']);
					array_push($Tags, $Tag);
					}					

				$API = array();
				$API['name'] = $Company_Name;
				$API['description'] = $Body;
				$API['image'] = trim($Logo_Image_Path);
				$API['humanURL'] = trim($Website_URL);
				
				
				if($Base_URL!='')
					{
					$API['baseURL'] = trim($Base_URL);
					}
				elseif($Developer_URL!='' && $Base_URL == '')				
					{
					$API['baseURL'] = trim($Developer_URL);
					}					
				else				
					{
					$API['baseURL'] = trim($Website_URL);
					}
									
				$API['tags'] = $Tags;

				$API['properties'] = array();
				
			    if($Twitter_URL!='')
					{
					$Link = array();
					$Link['type'] = "X-twitter";
					$Link['url'] = trim($Twitter_URL);
					array_push($API['properties'], $Link);
					}
					
			    if($Blog_URL!='')
					{
					$Link = array();
					$Link['type'] = "X-blog";
					$Link['url'] = trim($Blog_URL);
					array_push($API['properties'], $Link);
					}						

			    if($Blog_RSS_URL!='')
					{
					$Link = array();
					$Link['type'] = "X-blogrss";
					$Link['url'] = trim($Blog_RSS_URL);
					array_push($API['properties'], $Link);
					}

			    if($Github_URL!='')
					{
					$Link = array();
					$Link['type'] = "X-github";
					$Link['url'] = trim($Github_URL);
					array_push($API['properties'], $Link);
					}	
							
			    if($Developer_URL!='')
					{
					$Link = array();
					$Link['type'] = "X-developer-portal";
					$Link['url'] = trim($Developer_URL);
					array_push($API['properties'], $Link);
					}	
									
				$API['contact'] = array();		
				$Contact = array();
				$Contact['FN'] = $Company_Name;
				if($Email_Address!='')
					{
					$Contact['email'] = $Email_Address;
					}						
				if($Twitter_URL!='')
					{
					$Contact['X-twitter'] = $Twitter_URL;
					}				
				array_push($API['contact'], $Contact);
				
				// End Each API
				// Append
				array_push($APIJSON['apis'], $API);	
					
				$APIJSON['maintainers'] = array();
		
				$Maintainer = array();
				$Maintainer['FN'] = "Kin";
				$Maintainer['X-twitter'] = "apievangelist";
				$Maintainer['email'] = "kin@email.com";
		
				array_push($APIJSON['maintainers'], $Maintainer);	
				
				$ReturnEachAPIJSON = stripslashes(format_json(json_encode($APIJSON)));
																							
				}
			}
		}	

	$ReturnObject = $ReturnEachAPIJSON;
	$Parameters = "nothing";

	$ReturnObject['content'] = $Parameters;
	
	$app->response()->header("Content-Type", "application/json");
	echo stripslashes(format_json(json_encode($ReturnObject)));		

	});	
?> 