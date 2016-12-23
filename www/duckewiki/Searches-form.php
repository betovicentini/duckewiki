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
$body='';

HTMLheaders($body);

echo "<br>
<table class='tableform' align='center' >
<tr >
<td colspan=3 class='tabhead'>".GetLangVar('messagebuscarplantaspor')."</td>
</tr>
<tr class='tabhead'><td colspan=3><table align='center'><tr>
<form action='Searches-form.php' method='post'>
	<input type=hidden name='tipobusca' value='nomecientifico'>
	<td><input type='submit' class='bsubmit' value=".GetLangVar('namenomecientifico')."></td>
</form>	
<form action='Searches-form.php' method='post'>
	<input type=hidden name='tipobusca' value='vernacular'>
	<td><input type='submit' class='borange' value=".GetLangVar('namevernacular')."></td>
</form>
<form action='Searches-form.php' method='post'>
	<input type=hidden name='tipobusca' value='plantas'>
	<td><input type='submit' class='bblue' value=".GetLangVar('nametagnumber')."></td>
</form>		
</tr>
</table></td></tr>
<tr><td>&nbsp;</td></tr>
";

if ($tipobusca=='nomecientifico') {
		echo "<tr>
		<td colspan=3>
		<table align='center'><tr>
		<td class='tdsmallbold'>".GetLangVar('namenomecientifico')."</td></tr>
		<form action='search-name-exec.php' method='post'>
		<tr>
		<td>";
		autosuggestfieldval('search-name-simple.php','nomesearch',$nomesci,'nomescires','nomesciid');
		echo "
		</td>
		</tr>
		<tr>
		<td align='center'><input type='submit' class='bsubmit' value='".GetLangVar('nameenviar')."'></td>
		</tr>
		</form>
		</table>
		</td></tr>";
}

if ($tipobusca=='vernacular') {
		echo "<tr>
		<td colspan=3>
		<table align='center'><tr>
		<td class='tdsmallbold'>".GetLangVar('namevernacular')."</td></tr>
		<form action='search-vernacular-exec.php' method='post'>
		<tr>
		<td>";
		autosuggestfieldval('search-vernacular.php','vernacularsearch',$vernacular,'vernacularres','vernacularid');
		echo "
		</td>
		</tr>
		<tr>
		<td align='center'><input type='submit' class='bsubmit' value='".GetLangVar('nameenviar')."'></td>
		</tr>
		</form>
		</table>
		</td></tr>";
}
echo "</table>";

HTMLtrailers();

?>