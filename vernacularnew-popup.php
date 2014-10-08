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

$body= '';
$title = GetLangVar('namenovo')." ".GetLangVar('namevernacular');

PopupHeader($title,$body);
echo "
<br>
<table align='center' class='tableform' cellpadding='5'>";
echo "<tr><td>
<table align='center' class='tableform' cellpadding='3'>
<tr class='tabhead'>
<td colspan=100%>";
	unset($_SESSION['editando']);
	echo GetLangVar('namenovo')." ".strtolower(GetLangVar('namecadastro'));
echo "</td></tr>
<form action=vernacularnewpopup-exec.php method='post'>
	<input type='hidden' value='$vernacularid' name='vernacularid'>			

	<tr>
   	<td class='tdsmallbold' align='right'>".GetLangVar('namenome')."*</td>
   	<td class='tdformnotes' colspan=2><input type='text' name='nome' size='30%' value='$nome'></td>
   	</tr>
   	<tr>
	<td class='tdsmallbold' align='right'>".GetLangVar('namelanguage')."</td>
	<td class='tdsmallbold' colspan=2>
		<input type='text' name='lingua' size='20%' value='$lingua'> ".strtolower(GetLangVar('nameor'))."
		<select name='lingua2'>";
			if (!empty($lingua)) {
				echo "<option>".GetLangVar('nameselect')."</option>";
			} else {
				echo "<option value=$lingua>".$lingua."</option>";
			}
			$qq = "SELECT DISTINCT Language FROM Vernacular ORDER BY Language";
			$rr = mysql_query($qq,$conn);
			while ($row = mysql_fetch_assoc($rr)) {
				echo "<option value=".$row['Language'].">".$row['Language']."</option>";
			}
	echo "</select></td>
	</tr>
    <tr>
		  <td class='tdsmallbold' align='right'>".GetLangVar('namesignificado')."</td>  
		  <td class='tdformnotes' colspan=2><textarea name='definicao' cols=40 rows=2 wrap=SOFT>$definicao</textarea></td>
	</tr>
   	<tr>
	<td class='tdsmallbold' align='right'>".GetLangVar('namereference')."</td>
	<td class='tdformnotes' colspan=2><input type='text' name='referencia' size='30%' value='$referencia'></td>
	</tr>	
    <tr>
		  <td class='tdsmallbold' align='right'>".GetLangVar('nameobs')."</td>  
		  <td class='tdformnotes' colspan=2><textarea name='obs' cols=40 rows=5 wrap=SOFT>$obs</textarea></td>
	</tr>
    <tr>
		  <td colspan=3 align='center'><input type='submit' class='bsubmit' value=".GetLangVar('namesalvar')."></td>
  </form>    
	</tr>
	 </table>
</td></tr>	 
";

echo "</table>";

PopupTrailers();

?>