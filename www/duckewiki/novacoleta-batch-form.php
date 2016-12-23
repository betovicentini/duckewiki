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

HTMLheaders($body);


$bgi=1;
echo "<br>
<table class='myformtable' align='left' cellpadding='4' width='80%' >
<thead>
<tr >
	<td colspan=100%>";
	echo GetLangVar('novaserie')."&nbsp;<img height=13 src='icons/icon_question.gif'";
	$help = GetLangVar('novaseriemessage');
	echo	" onclick=\"javascript:alert('$help');\">
	</td>
</tr>
</thead>
<tbody>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "<tr bgcolor = $bgcolor>
<form name='coletaform' action=novacoleta-batch-exec.php method='post'>
	<td class='tdsmallboldright'>".GetLangVar('namecoletor')."</td>
		<td >
			<select name='pessoaid'>";
			echo "<option value='' class='optselectdowlight'>".GetLangVar('nameselect')."</option>";
			$rrr = getpessoa('',$abb=TRUE,$conn);
			while ($row = mysql_fetch_assoc($rrr)) {
				echo "<option value=".$row['PessoaID'].">".$row['Abreviacao']."</option>";
			}
			echo "</select>";
echo "</td></tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "<tr bgcolor = $bgcolor>
<td class='tdsmallboldright'>	".GetLangVar('namenumber')."s</td>
	<td>
	<table cellpadding='3'>
	<tr >
		<td align='right'>".GetLangVar('namefrom')."</td><td><input type='text' name='colnumde' value='$colnumde' size=5></td>
		<td align='right'>".GetLangVar('nameto')."</td><td><input type='text' name='colnumate' value='$colnumate' size=5></td>
	<td class='tdsmallboldright'>".GetLangVar('namedata')."</td>
	<td><input name=\"datacol\" value=\"$datacol\" size=\"11\" readonly >
	<a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['coletaform'].datacol);return false;\" >
	<img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\">
	</a>
</td>
</tr></table></td></tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "<tr bgcolor = $bgcolor>
	<input type='hidden' name='addcolvalue' value='$addcolvalue'>
	<td class='tdsmallboldright'>".GetLangVar('nameaddcoll')."</td>
	<td >
	<table>
		<tr>
		<td class='tdformnotes' >
			<input type='text' name='addcoltxt' value='$addcoltxt' readonly>
		</td>
		<td><input type=button value=\"+\" class='bsubmit' ";
		$myurl ="addcollpopup.php?getaddcollids=$addcolvalue&formname=coletaform"; 		

		echo "	onclick = \"javascript:small_window('$myurl',350,280,'Add_from_Src_to_Dest');\">
		</td>
		</tr>
	</table>
	</td></tr>
";

//dados de localidade
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
	$qq = "SELECT * FROM GPS_DATA WHERE Type='Waypoint' Order by GPSName,DateOriginal,Name ASC";
	$res = mysql_query($qq,$conn);
	echo "<tr bgcolor = $bgcolor>
	<td class='tdsmallboldright'>GPS ponto&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = GetLangVar('localidadebatchnote');
	echo	" onclick=\"javascript:alert('$help');\">
	</td><td><table><tr>
	<td colspan=3 id='locality' class='tdformnotes'>$locality</td>			
		<td class='tdformnotes'>"; autosuggestfieldval('search-gpspoint.php','gpspt',$gpspt,'gpsres','gpspointid',true); 
		echo "</td><td align='left' class='tdformnotes'>*selecione da lista</td>
			<td class='tdsmallboldright' align='center'>".mb_strtolower(GetLangVar('nameor')." ".GetLangVar('namelocalidade'))."</td>
			<td align='center'>
			<input type='hidden' id='gazetteerid'  name='gazetteerid' value='$gazetteerid'>
			<input type=button value='".GetLangVar('nameselect')."' class='bsubmit' 
					onclick = \"javascript:small_window('localidade-popup.php?gaztag=gazetteerid&localtag=locality&gazetteerid=$gazetteerid',850,150,'LocalidadePopUp');\">
				</td>
			</tr></table></td>
</tr>";

//habitat descricao
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
	else{$bgcolor = $linecolor1 ;}
	$bgi++;
	echo "<tr bgcolor = $bgcolor>
	<td class='tdsmallboldright'>".GetLangVar('namehabitat')."</td>	
	<td >
		<table align='left' cellpadding=\"3\" cellspacing=\"0\" class='tdformnotes'>
			<input type='hidden' id='habitatid'  name='habitatid' value='$habitatid'>

			<tr><td id='habitat'>$habitat</td>";
			$myurl = "habitat-popup.php";
			if (empty($habitatid)) {
				$butname = GetLangVar('nameselect');
			} else {
				$butname = GetLangVar('nameeditar');
			} 
			echo "<td>
			<input type=button value='$butname' class='bsubmit' onclick = \"javascript:small_window('$myurl',850,400,'HabitatPopUp');\">		
		</td></tr>
		</table>	
	</td>
	</tr>";
	
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
	else{$bgcolor = $linecolor1 ;}
	$bgi++;
echo "<tr bgcolor = $bgcolor>
	<td class='tdsmallboldright'>".GetLangVar('nameprojeto')."</td>
	<td >
		<select name='projetoid' >";
			if ($projetoid==0 || empty($projetoid)) {
				echo "<option>".GetLangVar('nameselect')."</option>";
			} else {
				$qq = "SELECT * FROM Projetos WHERE ProjetoID='".$projetoid."'";
				$prjres = mysql_query($qq,$conn);
				$prjrow = mysql_fetch_assoc($prjres);
				echo "<option  selected value='".$prjrow['ProjetoID']."'>".$prjrow['ProjetoNome']."</option>";
			}
			echo "<option>----</option>";
			$qq = "SELECT * FROM Projetos ORDER BY ProjetoNome";
			$resss = mysql_query($qq,$conn);
			while ($rwww = mysql_fetch_assoc($resss)) {
				echo "<option   value='".$rwww['ProjetoID']."'>".$rwww['ProjetoNome']."</option>";
			}
	echo "</select>
	</td>
	</tr>";
	
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
	else{$bgcolor = $linecolor1 ;}
	$bgi++;
	echo "<tr bgcolor = $bgcolor>
	<td colspan=100%>
		<table align='center' ><tr>
			<td align='center' >
				<input type='submit' value='".GetLangVar('namegerar')." ".GetLangVar('nameformulario')."' class='bsubmit' >
			</td>
</form>	

		</tr>
	</table>
</td>
</tr>
";

echo "</tbody></table>";
HTMLtrailers();

?>