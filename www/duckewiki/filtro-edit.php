<?php
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
include "functions/ImportData.php";

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

//echopre($_POST);
//$final=20; //do not process data
if ($final==2) {
	$qq = "DELETE FROM FiltrosSpecs WHERE FiltroID=".$_POST['filtro'];
	@mysql_query($qq,$conn);
	$qq = "DELETE FROM Filtros WHERE FiltroID=".$_POST['filtro'];
	$newfiltro = mysql_query($qq,$conn);
	@unlink("temp/Filtro_ID-".$oldid.".json");
} else {
if ($final==1) {
	$specarr = array();
	foreach ($_POST as $kk => $vv) {
		if ($kk!='final' && $kk!='filtro') {
			$zz = explode("_",$kk);
			$nn = array($zz[1]+0);
			$specarr = array_merge((array)$specarr,(array)$nn);
		}	
	}
	$nspecs = count($specarr);
	if ($nspecs>=1) {
		$specimenesids = implode(";",$specarr);
		$oldid = $_POST['filtro'];
		//$arrayofvals = array('EspecimenesIDS' => $specimenesids);
		//$newfiltro = UpdateTable($oldid,$arrayofvals,'FiltroID','Filtros',$conn);
		//@unlink("temp/Filtro_ID-".$oldid.".json");
		//$filtronome = 'filtroid_'.$oldid;
		$sql = "DELETE FROM FiltrosSpecs WHERE FiltroID=".$oldid." AND EspecimenID>0 AND (PlantaID=0 OR PlantaID IS NULL)";
		@mysql_query($sql,$conn);
		foreach ($specarr as $spid) {
			$sql = "INSERT INTO FiltrosSpecs (EspecimenID,FiltroID) VALUES (".$spid.",".$oldid.")";
			mysql_query($sql,$conn);
		}
	} elseif ($nspecs==0) {
		$qq = "DELETE FROM FiltrosSpecs WHERE FiltroID=".$_POST['filtro'];
		@mysql_query($qq,$conn);
		$qq = "DELETE FROM Filtros WHERE FiltroID=".$_POST['filtro'];
		$newfiltro = mysql_query($qq,$conn);
		@unlink("temp/Filtro_ID-".$oldid.".json");
	}
	if ($newfiltro) {
			echo "<p class='success' >Filtro atualizado!</p>";
	}
}
if (!isset($filtro)) {
echo "<br>
<table class='myformtable' align='left' border=0 cellpadding=\"5\" cellspacing=\"0\" >
<thead>
<tr><td >".GetLangVar('nameeditar')." ".GetLangVar('namefiltro')."s
&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
	$help = strip_tags(GetLangVar('editarfiltro_help'));
	echo	" onclick=\"javascript:alert('$help');\"></td>
</tr>
</thead>
<tbody>";
echo "<form action='filtro-edit.php' method='post'>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
	else{$bgcolor = $linecolor1 ;}
	$bgi++;
echo "<tr bgcolor = $bgcolor>
<td ><table><tr><td class='tdsmallbold'>".GetLangVar('namefiltro')."</td>
	<td><select name='filtro' onchange='this.form.submit();'>";
		if (!empty($filtro)) {
			$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtroid."'";
			$res = @mysql_query($qq,$conn);
			$rr = @mysql_fetch_assoc($res);
			echo "<option selected value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
		}
			echo "<option selected value=''>".GetLangVar('nameselect')."</option>";
			$qq = "SELECT * FROM Filtros WHERE (AddedBy=".$_SESSION['userid']." OR Shared=1) ORDER BY FiltroName";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "<option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			}	

	echo "</select></td>
</tr>
</table></td></tr>
</table>
</form>";

} else {
	$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtro."'";
	$res = mysql_query($qq,$conn);
	$rr = mysql_fetch_assoc($res);
	$especimenesids= $rr['EspecimenesIDS'];
	$specarr = explode(";",$especimenesids);
	
	$qq = "SELECT * FROM Especimenes JOIN Pessoas ON ColetorID=PessoaID WHERE ";
	$i=1;
	$nn = count($specarr)-1;
	foreach ($specarr as $kk => $vv) {
		if ($i==1) {
			$qq = $qq." EspecimenID='".$vv."'";
		} else {
			$qq = $qq." OR EspecimenID='".$vv."'";
		}
		$i++;
	}
	$qq = $qq." ORDER BY Abreviacao,Number+0";
	$rs = mysql_query($qq,$conn);
	$nsamp = mysql_numrows($rs);
	echo "
<br>
<form name='finalform' action=filtro-edit.php method='post'>
<table>
<tr>
			<td align='right' >
				<input type='submit' value='".GetLangVar('namedeselect')."' class='bsubmit' onclick=\"javascript:document.finalform.final.value=3\">
			</td>
			<td align='right' >
				<input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' onclick=\"javascript:document.finalform.final.value=1\">
			</td>	
			<td align='left' >
				<input type='submit' value='".GetLangVar('nameapagar')."' class='borange' onclick=\"javascript:document.finalform.final.value=2\">
			</td>
				
	</tr>
	<tr><td colspan=3>
<table class='sortable autostripe' cellspacing='0' cellpadding='3' align='center' width=1000>
<thead >
<tr>
<th>No filtro?</th>
<th align='center'>".GetLangVar('namecoletor')."</th>
<th align='center'>".GetLangVar('namenumber')."</th>
<th align='center'>".GetLangVar('nametaxonomy')."</th></tr></thead><tbody>";

echo "
<input type=hidden value=$filtro name='filtro'>
";
	$colect = '';
	$i=1;
	while ($row = mysql_fetch_assoc($rs)) {
			$cl = $row['Abreviacao'];
			$num = $row['Number'];
			$specid = $row['EspecimenID'];
			$detid = $row['DetID'];
			if ($detid>0) {
				$detarr = getdetsetvar($detid,$conn);
				$detset = serialize($detarr);
				$dettext = describetaxa($detset,$conn);
			}
			echo "<tr><td >$i<input type='checkbox' name='infiltro_".$specid."' "; 
			if ($final!=3)  { echo " checked"; }
			echo " value='1'></td>
					<td>".$cl."</td><td>".$num."<td>".$dettext."</td></tr>";
			$i++;
	}
	
	

	
	echo "</tbody></table></td></tr>";

	echo "<tr>
			<input type='hidden' name='final' value=''>
			<td align='right' >
				<input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' onclick=\"javascript:document.finalform.final.value=1\">
			</td>	
			<td align='left' >
				<input type='submit' value='".GetLangVar('nameapagar')."' class='boragne' onclick=\"javascript:document.finalform.final.value=2\">
			</td>	
		</tr>
	</table>
	</form>
	";
}
}
HTMLtrailers();

?>