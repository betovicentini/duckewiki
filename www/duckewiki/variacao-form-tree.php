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
<table align='left' class='myformtable' cellpadding='4'>
<thead>
<tr ><td colspan=100%>".GetLangVar('messageentrarvariacao')." ".GetLangVar('nametree')."</i></td></tr>
</thead>
<tbody>

<form action='variacao-form-tree.php' method='post'  >
	<input type='hidden' name='option1' value='1'>
<tr>
<td>
	<table>
	<tr>
		<td class='bold'>".GetLangVar('nameformulario')."</td>
		<td >
			<select name='formid'>";
				if ($formid>0) {
					$qq = "SELECT * FROM Formularios WHERE FormID='$formid'";
					$rr = mysql_query($qq,$conn);
					$row= mysql_fetch_assoc($rr);
					echo "<option selected value='".$row['FormID']."'>".$row['FormName']."</option>";
				} else {
					echo "<option value=''>".GetLangVar('nameselect')."</option>";
				}
				//formularios usuario
				$qq = "SELECT * FROM Formularios WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FormName ASC";
				$rr = mysql_query($qq,$conn);
				while ($row= mysql_fetch_assoc($rr)) {
					echo "<option value='".$row['FormID']."'>".$row['FormName']."</option>";
				}
			echo "
			</select>
		</td></tr>
	</table>
</td>
<td>
	<table>
		<tr>
	<td class='bold'>".GetLangVar('nametaggedplant')."</td>
	<td class='tdformnotes'>"; 
		autosuggestfieldval('search-plantas.php','plantatag',$plantatag,'plantares','plantaid',true); 
		echo "</td>
	</tr>
	</table>
</td>
<td align='center'>
<input type='submit' value='".GetLangVar('nameatualizar')."' class='bsubmit'>
</td>
</tr>
</form>
";

//IF FORMULARIO E LINK SELECIONADOS
if (!empty($formid) && $option1=='1' && is_numeric($formid)) {
$qq = "SELECT * FROM Plantas WHERE PlantaID='".$plantaid."'";
$rq = mysql_query($qq,$conn);
$rqw = mysql_fetch_assoc($rq);
$detid = $rqw['DetID'];
$dettaxa = getdet($detid,$conn);
$detnome = $dettaxa[0];
$detdetby = trim($dettaxa[1]);
$familia = strtoupper(trim($dettaxa[2]));
$dettext = $familia."  ".$detnome;
if (!empty($detdetby)) { $dettext =$dettext." <br>Det por: ".$detdetby.")";}

echo "
<thead>
<tr class='subhead'>
	<td colspan=100%>
		<table cellpadding='2' align='center'>
		<tr>
			<td >".GetLangVar('messageentrandodadospara')."&nbsp;&nbsp;&nbsp;&nbsp;</td>";
			$oldvals = EnteringVarFor($especimenid,$plantaid,$infraspid,$speciesid,$genusid,$famid,$conn);
			@extract($oldvals);
			echo "<td>".$dettext."</td>";
echo "</tr></table>
	</td>
</tr>
</thead>
<tbody>";
$actiontofile = 'variacao-exec-tree.php';
$actionfilereset = 'variacao-form-tree.php';
echo "<tr>
<td  colspan=100% align='center' >
	<form id='varform2' method='POST' enctype='multipart/form-data' action='".$actiontofile."'>
		<input type='hidden' name='MAX_FILE_SIZE' value='10000000'>
		<input type='hidden' name='option1' value='2'>
		<input type='hidden' name='formid' value='".$formid."'>
		<input type='hidden' name='plantaid' value='".$plantaid."'>";

		include "variacao-form2.php";
echo "
</td></tr>"; //fecha tabela para conteudo do formulario
echo "
<tr>
	<td  colspan='100%' >
		<table align='center'>
			<tr>
				<td align='center' >
					<input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' >
				</td>
</form>
<form action='$actionfilereset' method='post' >
	<input type='hidden' name='formid' value='$formid'>
				<td align='left'>
					<input type='submit' value='".GetLangVar('namereset')."' class='bblue' >
				</td>
			</tr>
		</table>
	</td>
</tr>
</form>
<tr>
	<td  colspan='100%' class='tdformnotes'>
		<b>".GetLangVar('nameobs')."</b>: ".GetLangVar('messagemultiplevalues')."
	</td>
</tr>";			
	
}
echo "</tbody></table>"; //fecha tabela do formulario

HTMLtrailers();

?>

