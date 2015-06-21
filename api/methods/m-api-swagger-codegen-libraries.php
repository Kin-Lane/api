<?php
$route = '/api/swagger/codegen/libraries/';	;
$app->get($route, function ()  use ($app,$awsAccessKey,$awsSecretKey,$awsRootURL,$awsSiteBucket){
	
	$ReturnObject = array();
	
	$ReturnObject['code-libraries'] = array();
	$ReturnObject['code-libraries']['php'] = array();
	$ReturnObject['code-libraries']['python'] = array();
	$ReturnObject['code-libraries']['ruby'] = array();
		
 	$request = $app->request(); 
 	$param = $request->params();
	
	if(isset($param['url'])){ $url = $param['url']; } else { $url = '';}
	//echo $url . "<br />";
	
	$s3 = new S3($awsAccessKey, $awsSecretKey);			
	
	$folder = $url;
	$folder = str_replace("http://","",$folder);
	$folder = str_replace("https://","",$folder);
	$folder = str_replace("/"," ",$folder);
	$folder = str_replace("."," ",$folder);
	$folder = PrepareFileName($folder);	
	if(substr($folder, 0,1)=="-"){ $folder = substr($folder, 0,strlen($folder)-1); }
	if(substr($folder, 0,1)=="-"){ $folder = substr($folder, 0,strlen($folder)-1); }
	if(substr($folder, 0,1)=="-"){ $folder = substr($folder, 0,strlen($folder)-1); }
	
	$Swagger_Store = file_get_contents($url);
	$export_file_name = $folder . ".json";
	$local_file = "/var/www/html/kin_lane/api/temp/" . $export_file_name;	
	$tmpFile = fopen($local_file, "w") or die("Unable to open file!");
	fwrite($tmpFile, $Swagger_Store);
	fclose($tmpFile);		
	$fileName = "api-evangelist-site/codegen/swagger-backup/" . $export_file_name;
	if ($s3->putObjectFile($local_file, $awsSiteBucket, $fileName, S3::ACL_PUBLIC_READ)) { }else{ }

	$publishfolder = "/var/www/html/kin_lane/api/temp/" . $folder . "/";
	//echo $publishfolder . "<br />";
	
	// PHP
	$command = "java -jar /var/www/html/kin_lane/api/swagger-codegen/modules/swagger-codegen-cli/target/swagger-codegen-cli.jar generate -i " . $url . " -l php -o " . $publishfolder . "php";
	//echo $command . "<br />";
	$output = shell_exec($command);
	//echo $output . "<br />";
	
	// Python
	$command = "java -jar /var/www/html/kin_lane/api/swagger-codegen/modules/swagger-codegen-cli/target/swagger-codegen-cli.jar generate -i " . $url . " -l python -o " . $publishfolder . "python";
	//echo $command . "<br />";
	$output = shell_exec($command);
	//echo $output . "<br />";	
	
	// Ruby
	$command = "java -jar /var/www/html/kin_lane/api/swagger-codegen/modules/swagger-codegen-cli/target/swagger-codegen-cli.jar generate -i " . $url . " -l ruby -o " . $publishfolder . "ruby";
	//echo $command . "<br />";
	$output = shell_exec($command);
	//echo $output . "<br />";		
	
	// Java
	$command = "java -jar /var/www/html/kin_lane/api/swagger-codegen/modules/swagger-codegen-cli/target/swagger-codegen-cli.jar generate -i " . $url . " -l java -o " . $publishfolder . "java";
	//echo $command . "<br />";
	$output = shell_exec($command);
	//echo $output . "<br />";	
	
	// C#
	$command = "java -jar /var/www/html/kin_lane/api/swagger-codegen/modules/swagger-codegen-cli/target/swagger-codegen-cli.jar generate -i " . $url . " -l csharp -o " . $publishfolder . "csharp";
	//echo $command . "<br />";
	$output = shell_exec($command);
	//echo $output . "<br />";		
	
	// Objective-C
	$command = "java -jar /var/www/html/kin_lane/api/swagger-codegen/modules/swagger-codegen-cli/target/swagger-codegen-cli.jar generate -i " . $url . " -l objc -o " . $publishfolder . "objc";
	//echo $command . "<br />";
	$output = shell_exec($command);
	//echo $output . "<br />";		
	
	// Objective-C
	$command = "java -jar /var/www/html/kin_lane/api/swagger-codegen/modules/swagger-codegen-cli/target/swagger-codegen-cli.jar generate -i " . $url . " -l android -o " . $publishfolder . "android";
	//echo $command . "<br />";
	$output = shell_exec($command);
	//echo $output . "<br />";		
	
	// HTML
	//$command = "java -jar /var/www/html/kin_lane/api/swagger-codegen/modules/swagger-codegen-cli/target/swagger-codegen-cli.jar generate -i " . $url . " -l html -o " . $publishfolder . "html";
	//echo $command . "<br />";
	//$output = shell_exec($command);
	//echo $output . "<br />";	
	
	$tempfolder = "/var/www/html/kin_lane/api/temp/";
	
	// PHP 
	$language = "php";
	$zipfolder = $tempfolder . $folder . "/" . $language . "/";
	$zipfile = $folder . "-" . $language . ".zip";
	$zipfilepath = $tempfolder . $zipfile;
	$include_dir = true;	
	$fileName = "api-evangelist-site/codegen/" . $folder . "-" . $language . ".zip";	
	$zipresult = zipIt($zipfolder, $zipfilepath, false, array('.DS_Store'));
	if ($s3->putObjectFile($zipfilepath, $awsSiteBucket, $fileName, S3::ACL_PUBLIC_READ)) { }else{ }	
	$zip_path = $awsRootURL . $fileName;		
	$F = array();
	$F['icon-url'] = "https://s3.amazonaws.com/kinlane-productions/bw-icons/bw-" . $language . "-file.png";		
	$F['zip-url'] = $zip_path;					
	$ReturnObject['code-libraries'][$language] = $F;
	$command = "rm -rf " . $zipfilepath;
	$output = shell_exec($command);			
	
	// Python 
	$language = "python";
	$zipfolder = $tempfolder . $folder . "/" . $language . "/";
	$zipfile = $folder . "-" . $language . ".zip";
	$zipfilepath = $tempfolder . $zipfile;
	$include_dir = true;	
	$fileName = "api-evangelist-site/codegen/" . $folder . "-" . $language . ".zip";	
	$zipresult = zipIt($zipfolder, $zipfilepath, false, array('.DS_Store'));
	if ($s3->putObjectFile($zipfilepath, $awsSiteBucket, $fileName, S3::ACL_PUBLIC_READ)) { }else{ }	
	$zip_path = $awsRootURL . $fileName;		
	$F = array();
	$F['icon-url'] = "https://s3.amazonaws.com/kinlane-productions/bw-icons/bw-" . $language . "-file.png";		
	$F['zip-url'] = $zip_path;					
	$ReturnObject['code-libraries'][$language] = $F;
	$command = "rm -rf " . $zipfilepath;
	$output = shell_exec($command);		
	
	// Ruby 
	$language = "ruby";
	$zipfolder = $tempfolder . $folder . "/" . $language . "/";
	$zipfile = $folder . "-" . $language . ".zip";
	$zipfilepath = $tempfolder . $zipfile;
	$include_dir = true;	
	$fileName = "api-evangelist-site/codegen/" . $folder . "-" . $language . ".zip";	
	$zipresult = zipIt($zipfolder, $zipfilepath, false, array('.DS_Store'));
	if ($s3->putObjectFile($zipfilepath, $awsSiteBucket, $fileName, S3::ACL_PUBLIC_READ)) { }else{ }	
	$zip_path = $awsRootURL . $fileName;		
	$F = array();
	$F['icon-url'] = "https://s3.amazonaws.com/kinlane-productions/bw-icons/bw-" . $language . "-file.png";		
	$F['zip-url'] = $zip_path;					
	$ReturnObject['code-libraries'][$language] = $F;	
	$command = "rm -rf " . $zipfilepath;
	$output = shell_exec($command);		
	
	// Java 
	$language = "java";
	$zipfolder = $tempfolder . $folder . "/" . $language . "/";
	$zipfile = $folder . "-" . $language . ".zip";
	$zipfilepath = $tempfolder . $zipfile;
	$include_dir = true;	
	$fileName = "api-evangelist-site/codegen/" . $folder . "-" . $language . ".zip";	
	$zipresult = zipIt($zipfolder, $zipfilepath, false, array('.DS_Store'));
	if ($s3->putObjectFile($zipfilepath, $awsSiteBucket, $fileName, S3::ACL_PUBLIC_READ)) { }else{ }	
	$zip_path = $awsRootURL . $fileName;		
	$F = array();
	$F['icon-url'] = "https://s3.amazonaws.com/kinlane-productions/bw-icons/bw-" . $language . "-file.png";		
	$F['zip-url'] = $zip_path;					
	$ReturnObject['code-libraries'][$language] = $F;
	$command = "rm -rf " . $zipfilepath;
	$output = shell_exec($command);						
	
	// csharp 
	$language = "csharp";
	$zipfolder = $tempfolder . $folder . "/" . $language . "/";
	$zipfile = $folder . "-" . $language . ".zip";
	$zipfilepath = $tempfolder . $zipfile;
	$include_dir = true;	
	$fileName = "api-evangelist-site/codegen/" . $folder . "-" . $language . ".zip";	
	$zipresult = zipIt($zipfolder, $zipfilepath, false, array('.DS_Store'));
	if ($s3->putObjectFile($zipfilepath, $awsSiteBucket, $fileName, S3::ACL_PUBLIC_READ)) { }else{ }	
	$zip_path = $awsRootURL . $fileName;		
	$F = array();
	$F['icon-url'] = "https://s3.amazonaws.com/kinlane-productions/bw-icons/bw-" . $language . "-file.png";		
	$F['zip-url'] = $zip_path;					
	$ReturnObject['code-libraries'][$language] = $F;	
	$command = "rm -rf " . $zipfilepath;
	$output = shell_exec($command);		
	
	// objc 
	$language = "objc";
	$zipfolder = $tempfolder . $folder . "/" . $language . "/";
	$zipfile = $folder . "-" . $language . ".zip";
	$zipfilepath = $tempfolder . $zipfile;
	$include_dir = true;	
	$fileName = "api-evangelist-site/codegen/" . $folder . "-" . $language . ".zip";	
	$zipresult = zipIt($zipfolder, $zipfilepath, false, array('.DS_Store'));
	if ($s3->putObjectFile($zipfilepath, $awsSiteBucket, $fileName, S3::ACL_PUBLIC_READ)) { }else{ }	
	$zip_path = $awsRootURL . $fileName;		
	$F = array();
	$F['icon-url'] = "https://s3.amazonaws.com/kinlane-productions/bw-icons/bw-" . $language . "-file.png";		
	$F['zip-url'] = $zip_path;					
	$ReturnObject['code-libraries'][$language] = $F;
	$command = "rm -rf " . $zipfilepath;
	$output = shell_exec($command);	
	
	// android-java 
	$language = "android";
	$zipfolder = $tempfolder . $folder . "/" . $language . "/";
	$zipfile = $folder . "-" . $language . ".zip";
	$zipfilepath = $tempfolder . $zipfile;
	$include_dir = true;	
	$fileName = "api-evangelist-site/codegen/" . $folder . "-" . $language . ".zip";	
	$zipresult = zipIt($zipfolder, $zipfilepath, false, array('.DS_Store'));
	if ($s3->putObjectFile($zipfilepath, $awsSiteBucket, $fileName, S3::ACL_PUBLIC_READ)) { }else{ }	
	$zip_path = $awsRootURL . $fileName;		
	$F = array();
	$F['icon-url'] = "https://s3.amazonaws.com/kinlane-productions/bw-icons/bw-" . $language . "-file.png";		
	$F['zip-url'] = $zip_path;					
	$ReturnObject['code-libraries'][$language] = $F;	
	$command = "rm -rf " . $zipfilepath;
	$output = shell_exec($command);			
	
	// Clean House
	$cleanfolder = $tempfolder . $folder . "/";
	$command = "rm -rf " . $cleanfolder;
	$output = shell_exec($command);													
	
	$app->response()->header("Content-Type", "application/json");
	echo format_json(json_encode($ReturnObject));			
		
	});
?> 