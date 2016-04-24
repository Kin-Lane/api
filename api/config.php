<?php

$dbserver = "laneworks-2.cjgvjastiugl.us-east-1.rds.amazonaws.com";
$dbname = "apievangelist";
$dbuser = "laneworks-api";
$dbpassword = "h8fmtfYxs2LbYD";

// Make a database connection
mysql_connect($dbserver,$dbuser,$dbpassword) or die('Could not connect: ' . mysql_error());
mysql_select_db($dbname);

$datastore = "mysql";

$githuborg = "Kin-Lane";
$githubrepo = "api";

$guser = "kinlane";
$gpass = "eVyy{29iPY";

$three_scale_provider_key = "9c72d79253c63772cc2a81d4e4bd07f8";

$awsAccessKey = "AKIAIVHZOWYJ3P3IBXNA";
$awsSecretKey = "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";
$awsSiteBucket = "kinlane-productions";
$awsRootURL = "http://kinlane-productions.s3.amazonaws.com/";

$appidAdmin = "5ed48098";
$appkeyAdmin = "b6c8c8cba92815a6cdfe6e780bb0d2f5"
?>
