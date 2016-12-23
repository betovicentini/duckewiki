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

if (empty($getaddcollids)) {
	//$body = "onLoad=\"javascript:getvarfromform('getdata','getaddcollids','addcollids')\"";
} else {
	$body='';
}
$body='';
$title = GetLangVar('namecoletor');

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
	$rrr = getpessoa('',$abb=TRUE,$conn);
	while ($row = mysql_fetch_assoc($rrr)) {
		echo "<option value=".$row['PessoaID'].">".$row['Abreviacao']."</option>";
	}
echo "</select>
</td>
<td width='30' align='center'>
<input type='button' value=' >> ' class='breset' onClick=\"javascript:addSrcToDestList('vernacularpop');\">
<br>";
//if ($_SESSION['editando']!=1) {
	echo "<br>
<input type='button' value=' << ' class='breset' onclick=\"javascript:deleteFromDestList('vernacularpop');\">";
//}
echo "</td>
<td>
	<select name=destList multiple size=10>";
	if (!empty($getaddcollids)) {
		$addcollids = explode(";",$getaddcollids);
		//print_r($addcollids);
		foreach ($addcollids as $addcoid) {
			$rrr = getpessoa($addcoid,$abb=TRUE,$conn);
			$row = mysql_fetch_assoc($rrr);
			echo "<option value=".$row['PessoaID'].">".$row['Abreviacao']."</option>";
		}
	}
echo "</select>
</td>
</tr>
<tr>
<td colspan=3 align='center'><br>
<input type='button' value=".GetLangVar('nameenviar')." class='bsubmit' onClick =\"javascript:MyArray('vernacularpop','addcolname','addcolvalue','addcoltxt');\">
</td>
</tr>
</table>
</form>";

PopupTrailers();

?>

