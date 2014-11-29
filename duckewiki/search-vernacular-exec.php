<?php
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";

$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} 

$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);

HTMLheaders('');


$nome = trim($vernacularsearch);


$qq = "SELECT * FROM Vernacular WHERE Vernacular='".$nome."'";
	//echo $qq;
$res = mysql_query($qq,$conn);
$nr = mysql_numrows($res);
if ($nr>0) {
	$row = mysql_fetch_assoc($res);
	print_r($row);
} 
HTMLtrailers();

?>