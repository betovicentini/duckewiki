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


unset($_SESSION['editando']);

HTMLheaders('');


echo "
<br>
<table align='left' class='myformtable' cellpadding=\"5\">
<thead>
<tr>
<td colspan=100%>".GetLangVar('nameeditar')." ".mb_strtolower(GetLangVar('nameor')." ".GetLangVar('namecadastrar')." ".GetLangVar('namegazetteer'))."</td>
</tr>
<tr class='subhead' >
<td colspan=100% >".GetLangVar('messagefilterrecords')."</td>
</tr>
</thead>
<tbody>
<tr>
<form name='paisform' action=localidade-form.php method='post'>
	<input type='hidden' name='textoutput' value='$textoutput'>
<td class='tdsmallbold'>".GetLangVar('namepais')."</td>	
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
<form name='provinciaform' action=localidade-form.php method='post'>	
	<input type='hidden' name='paisid' value='$paisid'>
	<input type='hidden' name='textoutput' value='$textoutput'>

<td class='tdsmallbold'>".GetLangVar('namemajorarea')."</td>	
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

<form name='municipioform' action=localidade-form.php method='post'>	
	<input type='hidden' name='paisid' value='$paisid'>
	<input type='hidden' name='provinciaid' value='$provinciaid'>
	<input type='hidden' name='textoutput' value='$textoutput'>

<td class='tdsmallbold'>".GetLangVar('nameminorarea')."</td>	
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
</tbody>
<thead>
<tr class='subhead'>
<td colspan=100%>".GetLangVar('nameselect')." ".GetLangVar('namelocalidade')."</td>
</tr>
</thead>
<tbody>
<tr>
<form action=localidade-exec.php method='post'>
	<input type='hidden' name='paisid' value='$paisid'>
	<input type='hidden' name='provinciaid' value='$provinciaid'>
	<input type='hidden' name='municipioid' value='$municipioid'>
	<input type='hidden' name='formsubmited' value='editar'>
	<td class='tdsmallbold'>".GetLangVar('namelocalidade')."</td>	
	<td colspan=2>
	<select name='gazetteerid' onchange='this.form.submit();'>";
			if (empty($gazetteerid)) {
					echo "<option value=''>".GetLangVar('messageselecttoedit')."</option>";
			}
			//$res = listgazetteer($municipioid,$provinciaid,$conn);
			$res = listgazetteerNew($municipioid,$provinciaid,$conn);
			//$qq = "SELECT GazetteerID,Gazetteer,PathName,MenuLevel,GazetteerTIPOtxt as GazTipo FROM Gazetteer WHERE MunicipioID='".$municipioid."' ORDER BY PathName,GazetteerTIPOtxt,Gazetteer";
			//$res = @mysql_query($qq,$conn);
			//
			if ($res) {
				while ($aa = mysql_fetch_assoc($res)){
					$PathName = $aa['PathName'];
					$level = $aa['MenuLevel'];
					//$gaztipo = $aa['GazTipo'];
					if ($level==1) {
						$espaco='';
						echo "<option class='optselectdowlight' value=".$aa['GazetteerID'].">".$espaco." ".$aa['Gazetteer']."</option>";
					} else {
						$espaco = str_repeat('&nbsp;&nbsp;',$level);
						$espaco = $espaco.str_repeat('-',$level-1);
						//echo "<option value=".$aa['GazetteerID'].">$espaco".$gaztipo." ".$aa['Gazetteer']."</option>";
						echo "<option value=".$aa['GazetteerID'].">".$espaco." ".$aa['Gazetteer']."</option>";
					}
				}
			}
echo "</select>&nbsp;
	 </td>
</form>
<form action=localidade-exec.php method='post'>
	<input type='hidden' name='paisid' value='$paisid'>
	<input type='hidden' name='provinciaid' value='$provinciaid'>
	<input type='hidden' name='municipioid' value='$municipioid'>
	<input type='hidden' name='formsubmited' value='novo'>
<td colspan=3 align='left'>
	<input type=submit value='".GetLangVar('namenova')." ".mb_strtolower(GetLangVar('namegazetteer'))."' class='bsubmit'>
</td>
</form>
</tr></tbody>
</table>";


HTMLtrailers();

?>

