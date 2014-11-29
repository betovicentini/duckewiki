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
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);


$body='';
$title = '';

if (!empty($especimenid)) {
	$res = resumecoleta($especimenid, $conn);
	$text = $res['listoftraits'];
}

if (!empty($plantaid)) {
	$res = resumoplanta($plantaid, $conn);
	$text = $res['listoftraits'];
}


PopupHeader($title,$body);


echo "	
<table width=100% align='center'>
<tr><td align='right'>
<input type='button' value='x' class='breset' onClick =\"javascript:window.close();\">
<table align='center' cellpadding=\"5\" class='erro' align='left' width='100%'>
<tr ><td align='left' class='tdformnotes'>".$text."</td></tr>
</table>
</td>
</tr>
</table>
";

PopupTrailers();

?>

