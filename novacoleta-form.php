<?php
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
include_once("functions/class.Numerical.php") ;
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
<table align='center' cellpadding='4' class='myformtable' >
<thead>
<tr >
<td colspan='100%'>".GetLangVar('nameamostra')." ".strtolower(GetLangVar('namecoletada'))."</td>
</tr>
</thead>
<tbody>
<tr>
<td class='tdsmallbold' align='right'>".GetLangVar('namecoleta')."*</td>
<form action=novacoleta-exec.php method='post'>
<input type='hidden' name='submeteu' value='editando'>
<td class='tdformnotes'>"; autosuggestfieldval('search-specimen.php','specname',$specname,'specnameres','especimenid',true); echo "</td><td align='left'></td>
<td><input type=submit value='".GetLangVar('nameeditar')."' class='bsubmit'></td> 
</form>
<form action=novacoleta-exec.php method='post'>
<td>
<input type='hidden' name='submeteu' value='nova'>
<input type=submit value='".GetLangVar('namenova')."' class='bblue'> 
</td>
</form>
</tr>
<tr><td colspan='100%' class='tdformnotes'>*Digite apenas o número e selecione da lista para poder editar</td></tr>
</tbody>
</table>";

HTMLtrailers();

?>