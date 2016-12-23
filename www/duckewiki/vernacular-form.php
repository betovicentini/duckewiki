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
echo "
<br>
<table align='left' class='myformtable' cellpadding='5'>";
if (empty($submitted)) {
echo "<thead><tr>
<td colspan=100%>
".GetLangVar('namecadastrar')." ".mb_strtolower(GetLangVar('namevernacular'))."
</td>
</tr>
</thead>
<tbody>
<tr>
	<form action=vernacular-form.php method='post'>
			<input type='hidden' value='editando' name='submitted'>			
	<td class='tdformnotes'>
			<select name='vernacularid' onchange='this.form.submit()';>";
			if (!isset($vernacularid)) {
				echo "<option value=''>".GetLangVar('nameselect')." ".mb_strtolower(GetLangVar('nameeditar'))."</option>";
			} else {
				$wr = getvernacular($vernacularid,$conn);
				$ww = mysql_fetch_assoc($wr);
				echo "<option  selected value='".$ww['VernacularID']."'>".$ww['Vernacular']."</option>";
			}
			echo "<option value=''>----</option>";
			$wrr = getvernacular('',$conn);
			while ($aa = mysql_fetch_assoc($wrr)){
				echo "<option value='".$aa['VernacularID']."'>".$aa['Vernacular']."</option>";
			}
	echo "</select>
	</td>
</form>
	<form action=vernacular-form.php method='post'>
			<input type='hidden' value='novo' name='submitted'>			
	<td class='tdformnotes' align='center'>
			<input type='submit' value='".GetLangVar('namenovo')." ".GetLangVar('namecadastro')."' class='bsubmit'>
	</td>
	</form>
</tr>";
} else {

echo "<tbody>
<tr><td>
<table align='center' class='tableform' cellpadding='3'>
<tr class='tabhead'>
<td colspan=100%>";
if ($submitted=='editando') {
	$_SESSION['editando']=1;
	$wr = getvernacular($vernacularid,$conn);
	$ww = mysql_fetch_assoc($wr);
	$nome = $ww['Vernacular'];
	$lingua = $ww['Language'];
	$definicao = $ww['Definition'];
	$referencia = $ww['Reference'];
	$taxonomyids = $ww['TaxonomyIDS'];
	$obs = $ww['Notes'];
	echo GetLangVar('nameeditando')." ".mb_strtolower(GetLangVar('namecadastro'))." ".$nome;
} elseif ($submitted=='novo') {
	unset($_SESSION['editando']);
	echo GetLangVar('namenovo')." ".mb_strtolower(GetLangVar('namecadastro'));
}

if (!empty($taxonomyids)) {
	$specieslist = describetaxacomposition($taxonomyids,$conn,$includeheadings=TRUE);
}
	
echo "</td></tr>
<form name='vernacularform' action=vernacular-exec.php method='post'>
	<input type='hidden' value='$vernacularid' name='vernacularid'>			

	<tr>
   	<td class='tdsmallbold' align='right'>".GetLangVar('namenome')."*</td>
   	<td class='tdformnotes' colspan=2><input type='text' name='nome' size='30%' value='$nome'></td>
   	</tr>
   	<tr>
	<td class='tdsmallbold' align='right'>".GetLangVar('namelanguage')."</td>
	<td class='tdsmallbold' colspan=2>
		<input type='text' name='lingua' size='20%' value='$lingua'> ".mb_strtolower(GetLangVar('nameor'))."
		<select name='lingua2'>";
			if (!empty($lingua)) {
				echo "<option value=''>".GetLangVar('nameselect')."</option>";
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
	</tr>";	
echo "<tr>
	<td class='tdsmallbold' align='right'>".GetLangVar('namespecies')."</td>
	<td colspan=2>
		<table><tr>	
		<input type='hidden' id='specieslistids' name='taxonomyids' value='$taxonomyids'>";
		if (empty($specieslist)) {
			echo "<td><textarea rows=2 cols=50 id='specieslist' name='specieslist' readonly>$specieslist</textarea></td>";
		} else {
			echo "<td class='tdsmalldescription'>$specieslist
				 	<input type='hidden' id='specieslist' name='specieslist' value='$specieslist'></td>";
		}
		echo 
		"<td>
			<input type='button' value='<<' class='bsubmit' ";
			$myurl ="selectspeciespopup.php?formname=vernacularform&elementname=specieslistids&destlistlist=".$taxonomyids;
			echo " onclick = \"javascript:small_window('$myurl',500,400,'SelectSpecies');\">
		</td>
	</tr>
	</table>
	</td>
	</tr>";

    echo "<tr>
		  <td class='tdsmallbold' align='right'>".GetLangVar('nameobs')."</td>  
		  <td class='tdformnotes' colspan=2><textarea name='obs' cols=40 rows=5 wrap=SOFT>$obs</textarea></td>
	</tr>
    <tr>
    		<td>&nbsp;</td>
		  <td align='right'><input type='submit' class='bsubmit' value=".GetLangVar('namesalvar')."></td>
  </form>    
	  <form action=vernacular-form.php method='post'>
          <td align='left'><input type='submit' class='breset' value=".GetLangVar('namevoltar')."></td>
		</form>
	</tr>
	 </table>
</td></tr>	 
";
} //else if !empty($vernacularid)

echo "</tbody></table>";

HTMLtrailers();

?>