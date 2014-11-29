<?php
//Start session
set_time_limit(0);
//Start session
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
//$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);


$uuid = $_SESSION['userid'];
$lastname = $_SESSION['userlastname'];
$aclevel = $_SESSION['accesslevel'];


HTMLheaders($body);

$dd = scandir('./');
//echopre($dd);

foreach ($dd as $ff) {  //for each original image
		if ($ff!="." && $ff!="..") {		
				$zf = explode(".",$ff);
				if ($zf[1]=='php') {
					//echo $ff."<br>";
					$str = file_get_contents($ff);
					$bom = pack("CCC", 0xef, 0xbb, 0xbf);
					if (0 == strncmp($str, $bom, 3)) {
						echo "BOM detected - file $ff is UTF-8<br>";
						//$str = substr($str, 3);
					}
				}
		}
}


HTMLtrailers();
?>