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
$body= '';
$title = GetLangVar('namenova')." ".GetLangVar('namelocalidade');


PopupHeader($title,$body);

$query = "SELECT DISTINCT `".$plantagazfield."` as missgen FROM `".$tbname."`  WHERE `".$plantagazfield."`<>'' AND `".$plantagazfield."` IS NOT NULL AND `".$colname."`=0";
$res = mysql_query($query,$conn);
$nres = mysql_numrows($res);

//|| empty($gazetteertipo) || $gazetteertipo=='digite aqui um novo tipo' || $gazetteertipo==GetLangVar('nameselect')
if (!isset($enviado) || empty($parentgazid) ) {
echo "<br>
<form action='localidade-novapopup_batch.php' method='post'>
<input type='hidden' name='buttonidx' value='$buttonidx' >
<input type='hidden' name='paisid' value='$paisid' >
<input type='hidden' name='provinciaid' value='$provinciaid' >
<input type='hidden' name='municipioid' value='$municipioid' >
<input type='hidden' name='plantagazfield' value='$plantagazfield' >
<input type='hidden' name='tbname' value='$tbname' >
<input type='hidden' name='colname' value='$colname' >
<input type='hidden' name='enviado' value='1' >

<table align='center' class='myformtable' cellpadding='5'>
<thead>
  <tr><td colspan='100%'>$nres localidades serão cadastradas como sublocalidade de:</td></tr>
</thead>
<tbody>";
$rr = getpais($paisid,$conn);
$row = mysql_fetch_assoc($rr);
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "<tr bgcolor = $bgcolor>
<td class='tdsmallbold' align='right' >".GetLangVar('namepais')."</td><td align='left'><i>".$row['Country']."</i></td></tr>";
$rr = getprovincia($provinciaid,$paisid,$conn);
$row = mysql_fetch_assoc($rr);
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "<tr bgcolor = $bgcolor>
<td class='tdsmallbold' align='right'>".GetLangVar('nameprovincia')."</td><td align='left'><i>".$row['Province']."</i></td></tr>";
$rr = getmunicipio($municipioid,$provinciaid,$conn);
$row = mysql_fetch_assoc($rr);
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "<tr bgcolor = $bgcolor>
<td class='tdsmallbold' align='right'>".GetLangVar('namemunicipio')."</td><td align='left'><i>".$row['Municipio']."</i></td></tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "<tr bgcolor = $bgcolor>
<td class='tdsmallbold' align='right'>".GetLangVar('messagepertencea')." ".GetLangVar('namelocalidade')."</td>	
<td >
	<select name='parentgazid'>";
			if (empty($parentgazid)) {
					echo "<option value=''>".GetLangVar('novagazetteer2')."</option>";
			} else {
				$rr = getgazetteer($parentgazid,$municipioid,$conn);
				$row = mysql_fetch_assoc($rr);
				//echo "<option selected value='".$row['GazetteerID']."'>".$row['GazetteerTIPOtxt']." ".$row['Gazetteer']."</option>";
				echo "<option selected value='".$row['GazetteerID']."'>".$row['Gazetteer']."</option>";
			}
			echo "<option value=''>----</option>";
			$rr = getgazetteer('',$municipioid,$conn);
			$level = '';
			while ($aa = mysql_fetch_assoc($rr)){
					$PathName = $aa['PathName'];
					//$gaztipo = $aa['GazetteerTIPOtxt'];
					if ($level!=$PathName) {
						//echo "<option value='".$aa['GazetteerID']."'>".$gaztipo." ".$aa['Gazetteer']."</option>";
						echo "<option value='".$aa['GazetteerID']."'>".$aa['Gazetteer']."</option>";
					} else {
						$espaco = $espaco.'&nbsp;';
						//echo "<option value='".$aa['GazetteerID']."'>$espaco".$gaztipo." ".$aa['Gazetteer']."</option>";
						echo "<option value='".$aa['GazetteerID']."'>".$espaco." ".$aa['Gazetteer']."</option>";
					}
					$level=$PathName;
			}
echo "</select>
</td>
</tr>";
//if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
//echo "<tr bgcolor = $bgcolor>
//<td  class='tdsmallbold' align=right>Qual o tipo dessas localidades?</td>";
//echo "
//<td >
//<table>
//<tr><td>";
//	echo "<select id='gaztipotxt' onchange=\"javascript:getselectoptionsendtoinput('gaztipotxt','gazetteertipo');\">";
//		echo"<option value=''>".GetLangVar('nameselect')."</option>";	
//		$qqq = "SELECT DISTINCT GazetteerTIPOtxt FROM Gazetteer ORDER BY GazetteerTIPOtxt";
//		$sql = mysql_query($qqq,$conn);
//		while ($aa = mysql_fetch_assoc($sql)){
//			echo "<option value=".$aa['GazetteerTIPOtxt'].">".$aa['GazetteerTIPOtxt']."</option>";
//		}
//		if (empty($gazetteertipo)) {
//			$gazetteertipo = 'digite aqui um novo tipo';	
//		}
//		echo "</select>
//		</td>
//			<td class='tdsmallbold' align=center>".strtolower(GetLangVar('nameor'))."</td>		
//		<td colspan=3 align='left'>
//			<input type='text' size='15' id='gazetteertipo' name='gazetteertipo' value='$gazetteertipo'>";
//echo "</td></tr>
//	</table>
//</td></tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "<tr bgcolor = $bgcolor>
<td colspan=100% align='center'>
<table>
<tr>
  <td><input type='submit' value='".GetLangVar('namecadastrar')."' class='bsubmit'>
</td>
</form>
<form >
<td><input type='button' value='".GetLangVar('namefechar')."' class='breset' onclick = \"javascript:window.close();\"></td>
</form>
</tr>
</table>
</td>
</tr>
<tbody>
</table>";
} else {
	while ($row = mysql_fetch_assoc($res)) {
		$fieldsaskeyofvaluearray = array(
			'ParentID' => $parentgazid,
			'Gazetteer' => trim($row['missgen']),
			'MunicipioID' => $municipioid);
			//'GazetteerTIPOtxt' => $gazetteertipo);
		$newgazid = InsertIntoTable($fieldsaskeyofvaluearray,'GazetteerID','Gazetteer',$conn);
		if ($newgazid) {
			UpdateGazetteerPath($newgazid,$conn);
			$qq = "UPDATE `".$tbname."` as tb set tb.`".$colname."`=".$newgazid." where tb.`".$plantagazfield."`='".$row['missgen']."'";
			mysql_query($qq,$conn);
		}
	}

echo "
  <form >
    <script language=\"JavaScript\">
      setTimeout( function() { changebutton('".$buttonidx."','Foram cadastrados');},0.0001);
    </script>
  </form>";
}


PopupTrailers();

?>
