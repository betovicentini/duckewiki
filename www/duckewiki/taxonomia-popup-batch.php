<?php

session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";

$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if((!isset($uuid) || 
	(trim($uuid)=='')) && count($listsarepublic)==0) {
		header("location: access-denied.php");
	exit();
} 

$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);

$body= '';
$title = GetLangVar('nameidentificacao');


PopupHeader($title,$body);


if ($final=='1') {

	//incomple sem os seguintes campos
	if (empty($nomesciid)) {
		echo "<br><table cellpadding=\"1\" width='50%' align='center' class='erro'>
			<tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro1')."</td></tr>";
			if ( empty($nomesciid) ) {
				echo "<tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namenomecientifico')."</td></tr>";
			}
			echo " </table><br>";
			$erro++;
	} 
	if ($erro==0) {
		list($famid,$genusid,$speciesid,$infraspid) = gettaxaids($nomesciid,$conn);
		$detarray = array(
				'FamiliaID' => $famid,
				'GeneroID' => $genusid,
				'EspecieID' => $speciesid,
				'InfraEspecieID' => $infraspid,
				'DetbyID' => $determinadorid,
				'DetDate' => $datadet,
				'DetConfidence' => $detconfidence, 
				'DetModifier' => $detmodifier , 
				'RefColetor' => $refcoletor , 
				'RefColnum' => $refcolnum ,
				'RefHerbarium' => $refherbarium , 
				'RefHerbNum' =>$refherbnum , 
				'RefDetby' =>$refdetby , 
				'RefDetDate' =>$refdatadet , 
				'DetNotes' =>$detnotes);
		
		$detset =serialize($detarray);
		
		$nn = describetaxa($detset,$conn);
	
		//echo $idd."here";

		echo "
		<form>
		<input type='hidden' id='sendid' value='".$nn."'>
			<script language=\"JavaScript\">
			setTimeout(
				function() {
					sendval_innerHTML('sendid','dettext_".$idd."');
					sendvalclosewin('detset_".$idd."','$detset');
				}
				,0.0001);
			</script>
		</form>";	
			
		
	}
} else {

	if (!empty($detid)) {
		$detarr = getdetsetvar($detid,$conn);
		$detset = serialize($detarr);
		
		$famid = $detarr['FamiliaID' ];
		$genusid = $detarr[ 'GeneroID' ];
		$speciesid = $detarr['EspecieID' ];
		$infraspid = $detarr[ 'InfraEspecieID' ];
		$determinadorid = $detarr[ 'DetbyID']+0;
		$datadet = $detarr[ 'DetDate' ];
		$detconfidence = $detarr[ 'DetConfidence' ];
		$detmodifier = $detarr[ 'DetModifier'];
		$refcoletor = $detarr['RefColetor' ];
		$refcolnum = $detarr[ 'RefColnum' ];
		$refherbarium = $detarr[ 'RefHerbarium' ];
		$refherbnum = $detarr[ 'RefHerbNum' ];
		$refdetby = $detarr[ 'RefDetby'];
		$refdatadet = $detarr['RefDetDate' ];
		$detnotes = $detarr[ 'DetNotes'];	
		$nomesci = strip_tags(gettaxaname($infraspid,$speciesid,$genusid,$famid,$conn));
	}
	
	
	
	
echo "
<table class='myformtable' align='left' border=0 cellpadding=\"3\" cellspacing=\"0\" >
<thead>
<tr><td colspan=100%>".GetLangVar('nameidentificacao')."</td></tr>
</thead>
<tbody>";
echo "<form name='finalform' action='taxonomia-popup-batch.php' method='post'>
<input type=hidden name='final' value='1'>
<input type=hidden name='idd' value='$idd'>

";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
	else{$bgcolor = $linecolor1 ;}
	$bgi++;
echo "<tr bgcolor = $bgcolor>
<td colspan=100%><table><tr>
<td class='tdsmallboldleft'>".GetLangVar('namenomecientifico')."</td>
<td>"; autosuggestfieldval('search-name-simple.php','nomesci',$nomesci,'nomeres','nomesciid',true); 
echo "</td><td align='left'><img height=13 src=\"icons/icon_question.gif\" ";
		$help = GetLangVar('notaneedtoselect');
		echo	" onclick=\"javascript:alert('$help');\"></td>
	</tr>
</table></td></tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
	else{$bgcolor = $linecolor1 ;}
	$bgi++;
echo "<tr bgcolor = $bgcolor>
<td>
<table><tr>
	<td class='tdsmallboldleft'>".GetLangVar('indicedecerteza')."&nbsp;<img height=13 src=\"icons/icon_question.gif\" ";
		$help = GetLangVar('indicedecerteza_help');
		echo	" onclick=\"javascript:alert('$help');\"></td>
	<td class='tdformnotes'>
		<select name='detconfidence'>
			<option value=''>".GetLangVar('nameselect')."</option>
			<option ";
			if ($detconfidence==1) {echo "selected";}
			echo " value='1'>1 - ".GetLangVar('certezaabsoluta')."</option>
			<option ";
			if ($detconfidence==2) {echo "selected";}
			echo " value='2'>2 - ".GetLangVar('namecerteza')."</option>
			<option ";
			if ($detconfidence==3) {echo "selected";}
			echo " value='3'>3 - ".GetLangVar('muitoparecida')."</option>
			<option ";
			if ($detconfidence==4) {echo "selected";}
			echo " value='4'>4 - ".GetLangVar('naoponhomaonofog')."</option>
			<option ";
			if ($detconfidence==5) {echo "selected";}
			echo " value='5'>5 - ".GetLangVar('tenhoduvida')."</option>
		</select>
	</td>
	</tr>
</table>
</td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
	else{$bgcolor = $linecolor1 ;}
	$bgi++;
echo "<tr bgcolor = $bgcolor>
		<td>
		<table>
		<tr>
		<td class='tdsmallboldleft' >".GetLangVar('messagenamemodifier')."</td>
		<td><input type='radio' ";
		if (substr($detmodifier,0,2)=='cf') { echo "checked";}
		echo " name='detmodifier' value='cf.'>cf.&nbsp;<img height=13 src=\"icons/icon_question.gif\" ";
	$help = GetLangVar('namecf');
	echo	" onclick=\"javascript:alert('$help');\">&nbsp;&nbsp;
		</td>
		<td><input type='radio' ";
		if ($detmodifier=='aff.') { echo "checked";}
		echo " name='detmodifier' value='aff.'>aff.&nbsp;<img height=13 src=\"icons/icon_question.gif\" ";
	$help = GetLangVar('nameaff');
	echo	" onclick=\"javascript:alert('$help');\">&nbsp;&nbsp;
		</td>
		<td><input type='radio' ";
		if ($detmodifier=='s.s.') { echo "checked";}
		echo " name='detmodifier' value='s.s.'>s.s.&nbsp;<img height=13 src=\"icons/icon_question.gif\" ";
	$help = GetLangVar('namess');
	echo	" onclick=\"javascript:alert('$help');\">&nbsp;&nbsp;
		</td>
		<td><input type='radio' ";
		if ($detmodifier=='s.l.') { echo "checked";}
		echo " name='detmodifier' value='s.l.'>s.l.&nbsp;<img height=13 src=\"icons/icon_question.gif\" ";
	$help = GetLangVar('namesl');
	echo	" onclick=\"javascript:alert('$help');\">&nbsp;&nbsp;
		</td>
		<td><input type='radio' ";
		if ($detmodifier=='vel aff.') { echo "checked";}
		echo " name='detmodifier' value='vel aff.'>vel aff.&nbsp;<img height=13 src=\"icons/icon_question.gif\" ";
	$help = GetLangVar('namevelaff');
	echo	" onclick=\"javascript:alert('$help');\">&nbsp;&nbsp;
		</td>
		<td><input type='radio' ";
		if (empty($detmodifier) || $detmodifier==' ') { echo "checked";}
		echo " name='detmodifier' value=' '>none&nbsp;</td>
		</tr></table>
</td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
	else{$bgcolor = $linecolor1 ;}
	$bgi++;
echo "<tr bgcolor = $bgcolor>
<td colspan=100%>
	<table  class='tablethinborder' align='center' width=99% cellpadding=\"3\" cellspacing=\"0\">
	<tr>
		<td colspan='100%' class='tabsubhead'>".GetLangVar('messageidbasedon')."&nbsp;<img height=13 src=\"icons/icon_question.gif\" ";
		$help = GetLangVar('messageidbasedon_help');
		echo	" onclick=\"javascript:alert('$help');\"></td>
	</tr>
	<tr>
		<td class='small'>".GetLangVar('namecoletor')."</td>
		<td class='small'>
			<select  id='pessoaid' name='refcoletor' >";	
			if (empty($refcoletor)) {
				echo "<option value=''>".GetLangVar('nameselect')."</option>";
			} else {
				$rr = getpessoa($refcoletor,$abb=true,$conn);
				$row = mysql_fetch_assoc($rr);
				echo "<option selected value=".$row['PessoaID'].">".utf8_encode($row['Abreviacao'])." (".utf8_encode($row['Prenome']).")</option>";
			}
			$rrr = getpessoa('',$abb=true,$conn);
			while ($row = mysql_fetch_assoc($rrr)) {
				$rv = trim($row['Abreviacao']);
				if (!empty($rv)) {
					echo "<option value=".$row['PessoaID'].">".utf8_encode($row['Abreviacao'])." (".utf8_encode($row['Prenome']).")</option>";
				}
			}
		echo "</select>
		</td>
		<td align=left><img src='icons/list-add.png' height=18 ";
		$myurl ="novapessoa-form-popup.php?pessoaid_val=pessoaid&secondid_val=refdetbyid_val"; 		
		echo " onclick = \"javascript:small_window('$myurl',500,350,'Nova Pessoa');\">
		</td>
		<td>&nbsp;</td>
		<td class='small'>".GetLangVar('namenumber')."</td>
		<td class='small'><input type='text' name='refcolnum' value='$refcolnum' size=5></td>
		<td class='small'>".GetLangVar('nameherbarium')." ".GetLangVar('namesigla')."</td>
		<td class='small'><input type='text' name='refherbarium' value='$refherbarium' size=4></td>
		<td class='small'>".GetLangVar('nameherbarium')." ".GetLangVar('namenumber')."</td>
		<td class='small'><input type='text' name='refherbnum' value='$refherbnum' size=4></td>
		</tr><tr><td colspan=100%><table><tr>
		<td class='small'>".GetLangVar('namedetby')."</td>
		<td class='small'>
		<select id='refdetbyid_val' name='refdetby' >";	
			if (empty($refdetby)) {
				echo "<option value=''>".GetLangVar('nameselect')."</option>";
			} else {
				$rr = getpessoa($refdetby,$abb=true,$conn);
				$row = mysql_fetch_assoc($rr);
				echo "<option selected value=".$row['PessoaID'].">".utf8_encode($row['Abreviacao'])." (".$row['Prenome'].")</option>";
			}
			$rrr = getpessoa('',$abb=true,$conn);
			while ($row = mysql_fetch_assoc($rrr)) {
				$rv = trim($row['Abreviacao']);
				if (!empty($rv)) {
					echo "<option value=".$row['PessoaID'].">".utf8_encode($row['Abreviacao'])." (".utf8_encode($row['Prenome']).")</option>";
				}
			}
		echo "</select>
		</td>
		<td class='small'>".GetLangVar('namedata')." Det.</td>
		<td class='small'>
			<table><tr>
			<td class='small'> <input name=\"refdatadet\" value=\"$refdatadet\" size=\"7\" ></td>
			<td class='small'>
				<a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['finalform'].refdatadet);return false;\" >
				<img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\"></a>
			</td></tr></table>
		</td>
		</tr></table></td>
	</tr>
	</table>
</td></tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
	else{$bgcolor = $linecolor1 ;}
	$bgi++;
echo "<tr bgcolor = $bgcolor><td><table>
<tr>
<td class='tdsmallboldleft'>".GetLangVar('nameobs')."</td>
<td>
<textarea name='detnotes' cols='80%' rows='2'>$detnotes</textarea>
</td></tr>
</table></td></tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
	else{$bgcolor = $linecolor1 ;}
	$bgi++;
echo "<tr bgcolor = $bgcolor>
	<td colspan=100%>
		<table  align='left' border=0 cellpadding=\"3\" cellspacing=\"0\">
		<tr>
			<td class='tdsmallboldright'>".GetLangVar('namedeterminador')."</td>
			<td class='tdformnotes'>
				<select name='determinadorid'>";
				if ($determinadorid>0) {
					echo "<option selected value=''>".GetLangVar('nameselect')."</option>";
				} else {
					$rr = getpessoa($determinadorid,$abb=true,$conn);
					$row = mysql_fetch_assoc($rr);
					echo "<option selected value=".$row['PessoaID'].">".utf8_encode($row['Abreviacao'])." (".utf8_encode($row['Prenome']).")</option>";
				}
				$rrr = getpessoa('',$abb=true,$conn);
				while ($row = mysql_fetch_assoc($rrr)) {
					$rv = trim($row['Abreviacao']);
					if (!empty($rv)) {
						echo "<option value=".$row['PessoaID'].">".utf8_encode($row['Abreviacao'])." (".utf8_encode($row['Prenome']).")</option>";
					}
				}
			echo "</select></td>
			<td class='tdsmallboldright'>".GetLangVar('namedata')."</td><td>
				<input class=\"plain\" name=\"datadet\" value=\"$datadet\" size=\"11\"  readonly>
				<a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['finalform'].datadet);return false;\" >
				<img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\"></a>
			</td>
		</tr></table></td></tr>
";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
	else{$bgcolor = $linecolor1 ;}
	$bgi++;
echo "<tr bgcolor = $bgcolor>		
<td colspan=100% align='center'>
<input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit'></td></tr>
</table>
</form>
";

}
PopupTrailers();
mysql_close();

?>