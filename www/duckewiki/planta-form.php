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

echo "<br>
<table align='center' cellpadding='5' class='myformtable'>
<thead>
<tr>
<td colspan=100%>".GetLangVar('nametaggedplant')."</td>
</tr>
</thead>
<tobdy>
<tr>
	<td class='tdsmallbold' align='right'>".GetLangVar('nametag')."</td>
";
echo "
	<form name='plantaform' action=planta-exec.php method='post'>
	<input type='hidden' name='submeteu' value=''>
	<td class='tdformnotes'>"; autosuggestfieldval('search-plantas.php','plantatag',$plantatag,'plantares','plantaid',true); echo "</td>
</tr>		
<tr><td align='center' colspan=2>
<table align='center'><tr>
<td align='center'>
	<input type='submit' value='".GetLangVar('nameeditar')."' class='bblue' onclick=\"javascript:document.plantaform.submeteu.value='editando'\">
</td>
<td align='center'>
	<input type='submit' value='".GetLangVar('namenova')."' class='bsubmit' onclick=\"javascript:document.plantaform.submeteu.value='nova'\">
</td>
</tr></table></td></tr>
</form>
</tbody>
</table>
";


HTMLtrailers();

?>