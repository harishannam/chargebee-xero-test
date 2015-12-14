<?php
function getDB() {
	$dbhost="128.199.198.232";
	$dbuser="xxxx";
	$dbpass='xxxx';
	$dbname="chargebee_xero";
	$dbConnection = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbConnection;
}
?>