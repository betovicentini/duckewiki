<?php

session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

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
$body='';
$title = GetLangVar('namevernacular');

PopupHeader($title,$body);


echo "
<form method='post' name='vernacularpop'>
<table class='tableform' align='center' cellpadding=\"5\">
<tr class='tabhead'>
<td width=150>".GetLangVar('namedisponivel')."</td>
<td width=20>&nbsp;</td>
<td width=150>".GetLangVar('nameselecionado')."</td>
</tr>
<tr>
<td>
<select name=srcList multiple size=10>";
	$wrr = getvernacular('',$conn);
			while ($aa = mysql_fetch_assoc($wrr)){
				echo "<option value='".$aa['VernacularID']."'>".$aa['Vernacular']."</option>";
	}
echo "</select>
</td>
<td width='30' align='center'>
<input type='button' value=' >> ' class='breset' onClick=\"javascript:addSrcToDestList('vernacularpop');\">
<br />";
//if ($_SESSION['editando']!=1) {
	echo "<br />
<input type='button' value=' << ' class='breset' onclick=\"javascript:deleteFromDestList('vernacularpop');\">";
//}
echo "</td>
<td>
	<select name=destList multiple size=10>";
	if (!empty($getvernacularids)) {
		$vernacularids = explode(";",$getvernacularids);
		//print_r($vernacularids);
		foreach ($vernacularids as $verid) {
			$wrr = getvernacular($verid,$conn);
			$aa = mysql_fetch_assoc($wrr);
			echo "<option value='".$aa['VernacularID']."'>".$aa['Vernacular']."</option>";
		}
	}
echo "</select>
</td>
</tr>
<tr>
<td colspan='3' align='center'><br />
<table align='center'><tr>
<td>";
echo "<input type='button' value=".GetLangVar('nameenviar')." class='bsubmit' onClick=\"javascript:MyArray('vernacularpop','$formname','$elementval','$elementtxt');\">";
echo
"</td>
<td>
<input type=button value=".GetLangVar('namenovo')."  class='bblue' ";
$myurl ="vernacular-dataform.php"; 
echo "	onclick = \"javascript:small_window('$myurl',500,500,'Vernacular');\">
</td>
<td>
<input type=button value='".GetLangVar('nameatualizar')." ".strtolower(GetLangVar('namelista'))."' class='borange' 	onclick = \"javascript:location.reload(true);\">
</td>
</tr></table>
</tr>
</table>
</form>";

PopupTrailers();

?>

