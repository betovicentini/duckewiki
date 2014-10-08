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


HTMLheaders($body);


//pega o coletor
$rr = getpessoa($pessoaid,$abb=FALSE,$conn);
$row = mysql_fetch_assoc($rr);
$coletor = $row['Abreviacao'];

echo "<br>
<table class='sortable autostripe' cellspacing='0' cellpadding='3' align='center' width=1000>
<thead >
<tr>
<th align='center'>".GetLangVar('namecoletor')."</th>
<th align='center'>".GetLangVar('namenumber')."</th>
<th align='center'>".GetLangVar('nametaxonomy')."</th>
<th align='center'>".GetLangVar('nameobs')."s</th>
<th align='center'>".GetLangVar('namedata')."</th>
<th align='center'>".GetLangVar('namelocalidade')."</th>
<th align='center'>".GetLangVar('namehabitat')."</th>
<th align='center'>".GetLangVar('nameaddcoll')."</th>";
if ($projetoid>0) {
	echo "<th align='center'>".GetLangVar('nameprojeto')."</th>";
}
echo "</tr>
</thead>
<tbody>";
echo "<form action='novacoleta-batch-store.php' method='post'>



";
for ($i=$colnumde;$i<=$colnumate;$i++) {

	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
		else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "<tr class='small' bgcolor = $bgcolor>";
	echo "<td>$coletor</td>";
	echo "<td>$i</td>";
	
	//taxonomy
	 echo "<td>
	<table class='dettable'><tr><td id='dettext_".$i."'>$dettext</td>
	<input type='hidden' id='detset_".$i."' name='detset_".$i."' value='$detset' >
	<td><input type=button value='+' class='bsubmit' ";
	$myurl ="taxonomia-popup-batch.php?detid=$detid&idd=$i"; 		
	echo "	onclick = \"javascript:small_window('$myurl',800,200,'Add_from_Src_to_Dest');\">
			</td></tr></table></td>";
			
	//notas
	$traivar = "$traitids_$i.";
	echo "<td><table class='dettable'><tr>
		<td id='traitids_".$i."'>$traitids</td><td>
		<input  type=button value='+' class='bsubmit' onclick = \"javascript:small_window('variacao-popup-batch.php?&elementid=traitids_".$i."',700,500,'EntrarVariacao');\">
		</td></tr></table></td>
	
	<td>$datacol</td>";
       
       	if ($gpspointid>0) {
		$locality = getGPSlocality($gpspointid,$name=FALSE,$conn);
	} elseif ($gazetteerid>0) {
		$locality = getlocality($gazetteerid,$coord=TRUE,$conn);
	}
	
        echo "<td><table class='dettable'><tr ><td id='locality_".$i."'>$locality</td></tr>
	<tr><table><tr><td>
		<select class='ftpequena' name='gpspointid_".$i."'>";
	if ($gpspointid>0) {
		$qq = "SELECT * FROM GPS_DATA WHERE PointID='$gpspointid'";
		$res = mysql_query($qq,$conn);
		$row = mysql_fetch_assoc($res);
		echo "<option value=".$row['PointID'].">".$row['Name']."</option>";
	}
	echo "<option value=''>Ponto GPS</option>";
	$qq = "SELECT * FROM GPS_DATA WHERE Type='Waypoint' Order by GPSName,DateOriginal,Name ASC";
	echo $qq;
	$res = mysql_query($qq,$conn);
	$gps = "nenhum";
	$date = "1900-10-04";
	while ($row = mysql_fetch_assoc($res)) {
		if ($gps!=$row['GPSName']) {
			$gps = $row['GPSName'];
			echo "<option class='optselectdowlight' value=''>".$row['GPSName']."------</option>";
		}
		if ($date!=$row['DateOriginal']) {
			$date = $row['DateOriginal'];
			echo "<option class='redtext' value=''>&nbsp;&nbsp;".$row['DateOriginal']."</option>";
		}
		echo "<option value=".$row['PointID'].">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$row['Name']."</option>";
	}
	echo "</select></td>
	<td class='ftpequena' align='center'>".strtolower(GetLangVar('nameor'))."</td>
			<td align='center'>
			<input type='hidden' id='gazetteerid_".$i."'  name='gazetteerid_".$i."' value='$gazetteerid'>
			<input class='ftpequena'  type=button value='".GetLangVar('namelocalidade')."' class='bsubmit' 
					onclick = \"javascript:small_window('localidade-popup.php?gaztag=gazetteerid_".$i."&localtag=locality_".$i."&gazetteerid=$gazetteerid',850,150,'LocalidadePopUp');\">
				</td>
				</tr><table>
	
	</tr></table></td>";
       
			
	//habitat
	$habitatvarid = "habitatid_$i";
	$habitatvar = "habitat_$i";
	if (empty($$habitatvarid) && !empty($habitatid)) { 
		$habitat= describehabitat($habitatid,$img=FALSE,$conn);
	}
	echo "<input type='hidden' id='habitatid_".$i."'  name='habitatid_".$i."' value='$habitatid'>
	<td><table class='dettable'><tr>
		<td id='habitat_".$i."'>$habitat</td><td>";
	$myurl = "habitat-popup-batch.php?idd=$i";
	echo "<input type=button value='+' class='bsubmit' onclick = \"javascript:small_window('$myurl',850,400,'HabitatPopUp');\"></td>
	</tr></table></td>
		
	<td>$addcoltxt</td>";
	if ($projetoid>0) {
				$qq = "SELECT * FROM Projetos WHERE ProjetoID='".$projetoid."'";
				$prjres = mysql_query($qq,$conn);
				$prjrow = mysql_fetch_assoc($prjres);
				$projeto = $prjrow['ProjetoNome'];
				echo "<td>$projeto</td>";
	} 
	echo "	</tr>";
}
echo "
<tr><td colspan=100% align='center'>
	<input type='hidden' name='final' value='1'>
	<input type='hidden' name='pessoaid' value='$pessoaid'>
	<input type='hidden' name='colnumde' value='$colnumde'>
	<input type='hidden' name='colnumate' value='$colnumate'>
	<input type='hidden' name='addcolvalue' value='$addcolvalue'>
	<input type='hidden' name='gpspointid' value='$gpspointid'>
	<input type='hidden' name='gazetteerid' value='$gazetteerid'>
<input type='hidden' name='habitatid' value='$habitatid'> 
	<input type='hidden' name='datacol' value='$datacol'> 
	<input type='hidden' name='projetoid' value='$projetoid'> 
	
	<input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' >
			</td></tr>
</form>
</tbody>
</table>
";

HTMLtrailers();

?>