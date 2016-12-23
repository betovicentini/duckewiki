<?php
//Start session
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
include "functions/ImportData.php";
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
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);

HTMLheaders($body);

echo "<br>
<table class='myformtable' align='left' cellpadding=5>
<thead>
<tr class='tabhead'><td>
".GetLangVar('novotaxa8').":
</td>
</tr>
</thead>
<tbody>
<tr><td>
<table align='left'>
<form action=novoIPNItaxa-exec.php method='post'>
<tr>
   	<td class='tdformright'>".GetLangVar('namefamily')."</td>
   	<td class='tdformleft' colspan=2><input type='text' name='family' size='40%'></td>
</tr>
<tr>
	<td class='tdformright'>".GetLangVar('namegenus')."</td>
	<td class='tdformleft' colspan=2><input type='text' name='genus' size='40%'></td>
</tr>
<tr>
	<td>&nbsp;</td>
  <td class='tdformnotes' colspan=1><input type='checkbox' name='proxy'>Selecionar aqui se estiver no INPA (proxy)</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td align='center'><input type='submit' class='bsubmit' value=".GetLangVar('nameenviar')."></td>	
</form>

	
	<form action=novoIPNItaxa-form.php method='post'>
		<td align='center'><input type = 'submit' class='breset' value='".GetLangVar('namereset')."'></td>
	</form>
	</td>
</tr>

</table>
<tr class='tdformnotes'><td>
Pode demorar bastante dependendo do tamanho do taxa em número de espécies. 
<BR>Se for muito grande a importação pode ser apenas parcial (BUG?).
</td></tr>
</td></tr>
</tbody>
</table>
";
HTMLtrailers();

?>