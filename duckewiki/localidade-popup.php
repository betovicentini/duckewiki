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
$title = GetLangVar('nameselect')." ".GetLangVar('namelocalidade');
PopupHeader($title,$body);


if ($gazetteerid>0 && $formsubmited=='1') {
		$qq = "SELECT * FROM Gazetteer JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID) WHERE GazetteerID='$gazetteerid'";
		$res = mysql_query($qq,$conn);
		$row = mysql_fetch_assoc($res);
		$municipioid = $row['MunicipioID'];
		$provinciaid = $row['ProvinceID'];
		$paisid = $row['CountryID'];
		
		$locality = getlocality($gazetteerid,$coord=TRUE,$conn);
		echo "
		<form  >
		<input type='hidden' id='sendid' value='".$locality."'>
		<input type='hidden' id='gazetteerid' value='".$gazetteerid."'>
			<script language=\"JavaScript\">
			setTimeout(
				function() {
					sendval_innerHTML('sendid','$localtag');
					sendvalclosewin('$gaztag','$gazetteerid');
				}
				,0.0001);
			</script>
		</form>";		
		
}

echo "
<table align='center' class='myformtable' cellpadding=\"4\">
<thead>
<tr>
<td colspan=100%>".GetLangVar('nameeditar')." ".strtolower(GetLangVar('nameor')." ".GetLangVar('namecadastrar')." ".GetLangVar('namegazetteer'))."</td>
</tr>
</thead>
<tbody>
<tr>
<form name='paisform' action=localidade-popup.php method='post'>
	<input type='hidden' name='textoutput' value='$textoutput'>
		<input type='hidden' name='gaztag' value='$gaztag'>
	<input type='hidden' name='localtag' value='$localtag'>
	<td class='tdformright'>".GetLangVar('namepais')."</td>	
<td >
	<select name='paisid' onchange='this.form.submit();'>";
			if (empty($paisid)) {
				$paisid=30; //Brasil				
			}	
			$rr = getpais($paisid,$conn);
			$row = mysql_fetch_assoc($rr);
			echo "<option selected value=".$row['CountryID'].">".$row['Country']."</option>";

			$rrr = getpais('',$conn);
			while ($row = mysql_fetch_assoc($rrr)) {
				echo "<option value=".$row['CountryID'].">".$row['Country']."</option>";
			}

echo "</select>
</td>
</form>
<form name='provinciaform' action=localidade-popup.php method='post'>	
	<input type='hidden' name='paisid' value='$paisid'>
	<input type='hidden' name='textoutput' value='$textoutput'>
		<input type='hidden' name='gaztag' value='$gaztag'>
	<input type='hidden' name='localtag' value='$localtag'>
<td class='tdformright'>".GetLangVar('namemajorarea')."</td>	
<td >
<select name='provinciaid' onchange='this.form.submit();'>";
			if (empty($provinciaid)) {
				echo "<option>".GetLangVar('nameselect')."</option>";
			} else {
				$rr = getprovincia($provinciaid,$paisid,$conn);			
				$row = mysql_fetch_assoc($rr);
				echo "<option selected value=".$row['ProvinceID'].">".$row['Province']."</option>";
			}
			$newrr = getprovincia('',$paisid,$conn);
			while ($row = mysql_fetch_assoc($newrr)) {
				echo "<option value=".$row['ProvinceID'].">".$row['Province']."</option>";
			}
echo "</select>
</td>
</form>

<form name='municipioform' action=localidade-popup.php method='post'>	
	<input type='hidden' name='paisid' value='$paisid'>
	<input type='hidden' name='provinciaid' value='$provinciaid'>
	<input type='hidden' name='textoutput' value='$textoutput'>
		<input type='hidden' name='gaztag' value='$gaztag'>
	<input type='hidden' name='localtag' value='$localtag'>
<td class='tdformright'>".GetLangVar('nameminorarea')."</td>	
<td >
	<select name='municipioid' onchange='this.form.submit();'>";
			if (empty($municipioid)) {
				echo "<option>".GetLangVar('nameselect')."</option>";
			} else {
				$rr = getmunicipio($municipioid,$provinciaid,$conn);			
				$row = mysql_fetch_assoc($rr);
				echo "<option selected value=".$row['MunicipioID'].">".$row['Municipio']."</option>";
			}
			$newrr = getmunicipio('',$provinciaid,$conn);
			while ($row = mysql_fetch_assoc($newrr)) {
				echo "<option value=".$row['MunicipioID'].">".$row['Municipio']."</option>";
			}
echo "</select>
</td>
</form>
</tr>
<tr>
<form action=localidade-popup.php method='post'>
	<input type='hidden' name='paisid' value='$paisid'>
	<input type='hidden' name='provinciaid' value='$provinciaid'>
	<input type='hidden' name='municipioid' value='$municipioid'>
	<input type='hidden' name='formsubmited' value='1'>
		<input type='hidden' name='gaztag' value='$gaztag'>
	<input type='hidden' name='localtag' value='$localtag'>
	<td class='tdformright'>".GetLangVar('namelocalidade')."</td>	
	<td colspan=5>
	<select name='gazetteerid' onchange='this.form.submit();'>";
			if (empty($gazetteerid)) {
					echo "<option>".GetLangVar('nameselect')."</option>";
			} else {
					//$qq = "SELECT GazetteerID,GazetteerTIPOtxt,Gazetteer FROM Gazetteer WHERE GazetteerID='$gazetteerid'";
					$qq = "SELECT GazetteerID,Gazetteer FROM Gazetteer WHERE GazetteerID='".$gazetteerid."'    ";
					$rr = mysql_query($qq,$conn);
					$rw = mysql_fetch_assoc($rr);
					echo "<option selected value='$gazetteerid'>".$rw['Gazetteer']."</option>";
//					echo "<option selected value='$gazetteerid'>".$rw['GazetteerTIPOtxt']." ".$rw['Gazetteer']."</option>";

			}
			//$qq = "SELECT GazetteerID,Gazetteer,PathName,MenuLevel,GazetteerTIPOtxt as GazTipo FROM Gazetteer WHERE MunicipioID='".$municipioid."' ORDER BY PathName,GazetteerTIPOtxt,Gazetteer";
			//$res = mysql_query($qq,$conn);
			$res = listgazetteerNew($municipioid,$provinciaid,$conn);
			while ($aa = mysql_fetch_assoc($res)){
					$PathName = $aa['PathName'];
					$level = $aa['MenuLevel'];
					//$gaztipo = $aa['GazTipo'];
					if ($level==1) {
						$espaco='';
						echo "<option class='optselectdowlight' value=".$aa['GazetteerID'].">".$espaco." ".$aa['Gazetteer']."</option>";
						//echo "<option class='optselectdowlight' value=".$aa['GazetteerID'].">$espaco".$gaztipo." ".$aa['Gazetteer']."</option>";
					} else {
						$espaco = str_repeat('&nbsp;&nbsp;',$level);
						$espaco = $espaco.str_repeat('-',$level-1);
						//echo "<option value=".$aa['GazetteerID'].">$espaco".$gaztipo." ".$aa['Gazetteer']."</option>";
						echo "<option value=".$aa['GazetteerID'].">".$espaco." ".$aa['Gazetteer']."</option>";

					}
			}
	
$myurl = "localidade-novapopup.php?municipioid=$municipioid&paisid=$paisid&provinciaid=$provinciaid";
echo "</select>&nbsp;
	<input type=button class='bblue'
	value='".GetLangVar('namenova')." ".strtolower(GetLangVar('namegazetteer'))."' 
	onclick =\"javascript:small_window('$myurl',900,300,'NovaGazetteer');\">
</td>
</form>
</tr>";

$locality = getlocality($gazetteerid,$coord=TRUE,$conn);
echo "<input type='hidden' id='sendid' value='".$locality."'>";
					
echo "
<tr>
<td align='center' colspan=6>
	<input type=button value='".GetLangVar('nameselect')."' class='bsubmit' 
	onclick=\"javascript:sendval_innerHTML('sendid','".$localtag."');sendvalclosewin('".$gaztag."','".$gazetteerid."');\">
</td>

</tr></tbody>
</table>";


PopupTrailers();

?>
