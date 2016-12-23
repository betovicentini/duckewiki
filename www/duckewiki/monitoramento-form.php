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

//echopre($_POST);
//echopre($_FILES);
if (isset($plantatag) && empty($plantaid)) {
	$plantaid = $flowplantaid;
} else {
	$flowplantaid=$plantaid;
}


echo "<br>
<table align='left' class='myformtable' cellpadding='4' width='100%'>
<thead>
<tr ><td colspan=100%>".GetLangVar('namemonitoramento')."</td></tr>
</thead>";
echo "<tbody>

<form  method='post'  action='monitoramento-form.php' name='inicioform'>	
<input type='hidden' name='option1' value='1'>
<input type='hidden' name='flowplantaid' value='$flowplantaid'>

<tr>
<td colspan=100%>
<table><tr>";

echo "	
<td>
	<table>
	<tr>
	<td class='bold'>".GetLangVar('nametaggedplant')."</td>
	<td><td class='tdformnotes'>"; autosuggestfieldval('search-plantas.php','plantatag',$plantatag,'plantares','plantaid',true); echo "</td>
	</tr>
	</table>
	</td>";

echo "<td>
	<table>
	<tr>
		<td class='bold'>".GetLangVar('nameformulario')."</td>	
		<td >
				<select name='formid' onchange='this.form.submit();'>";
				if (!empty($formid)) {
					$qq = "SELECT * FROM Formularios WHERE FormID='$formid'";
					$rr = mysql_query($qq,$conn);
					$row= mysql_fetch_assoc($rr);
					echo "<option selected value='".$row['FormID']."'>".$row['FormName']." (".$row['AddedDate'].")</option>";
				} else {
					echo "<option value=''>".GetLangVar('nameselect')."</option>";
				}
				//formularios usuario
				$qq = "SELECT * FROM Formularios WHERE AddedBy=".$_SESSION['userid']." OR Shared=1  ORDER BY FormName ASC";
				$rr = mysql_query($qq,$conn);
				while ($row= mysql_fetch_assoc($rr)) {
					echo "<option value='".$row['FormID']."'>".$row['FormName']."</option>";
				}
			echo "
			</select>
		</td></tr>
	</table>
</td>";
echo "<td>
	<table>
		<tr><td class='bold'>".GetLangVar('namedata')." OBS</td>
		<td><input name=\"dataobs\" value=\"$dataobs\" size=\"11\" readonly ></td><td>
		<a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['inicioform'].dataobs);return false;\" >
		<img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\"></a>
		</td>";
		if (empty($dataobs)) {
	echo "<td>&nbsp;</td>
		<td align='right' >
			<input type='submit' value='".GetLangVar('namecontinuar')."' class='borange'\">
		</td>";
		}
echo "</tr>
	</table>
	</td></tr>
</form>
</table></td></tr>";

//IF FORMULARIO E LINK SELECIONADOS
if (!empty($formid) && $option1=='1' && is_numeric($formid) && !empty($dataobs)) {
	$oldvals =  GetMonitoringData($plantaid,$dataobs,$formid,$conn);
	@extract($oldvals);
	$actiontofile = 'monitoramento-exec.php';
	$actionfilereset = 'monitoramento-form.php';
	echo "<tr>
<td  colspan=100% align='center' >
	<form id='varform2' method='POST' enctype='multipart/form-data' action='".$actiontofile."'>
		<input type='hidden' name='MAX_FILE_SIZE' value='10000000'>
		<input type='hidden' name='dataobs' value='".$dataobs."'>
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
if ($option1=='1' && $formid>0) {
	tableofmonitortraits($plantaid,$plantatag,$conn);
}
HTMLtrailers();

?>

