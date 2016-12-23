<?php
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
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


$qq = "SELECT DISTINCT hab.ParentID,hab.Habitat,hab.HabitatID,hab.PathName FROM Habitat as hab WHERE hab.ParentID='".$parentid."' ORDER BY hab.PathName";
//echo $qq."<br>";
$rz = @mysql_query($qq,$conn);
$nrz = @mysql_numrows($rz);
if ($nrz>20) {
	$selsize = 25;
} else {
	$selsize = ($nrz+1);
}
echo "
<br>
<table cellpadding='0' align='left' style='border: 0;'>
<tr>
<td style='font-size: 0.6em' align='left'>
<select id=\"habitatlist\" name='habitattoplot[]' size='".$selsize."' multiple >";
if ($nrz==0) {
echo "
<option selected value=''>Nenhum hábitat local encontrado</option>";
} else {
	while ($row = mysql_fetch_assoc($rz)) {
		$vv = $row['HabitatID'];
		echo "
<option value='".$vv."'>".$row['Habitat']."&nbsp;</option>";
		$qq = "SELECT DISTINCT hab.ParentID,hab.Habitat,hab.HabitatID,hab.PathName FROM Habitat as hab WHERE hab.ParentID='".$vv."' ORDER BY hab.PathName";
		$rzz = mysql_query($qq,$conn);
		$nrzz = mysql_numrows($rzz);
		if ($nrzz>0) {
			while ($rw = mysql_fetch_assoc($rzz)) {
			$vvv = $rw['HabitatID'];
			echo "
<option value='".$vvv."'>&nbsp;&nbsp;".$rw['Habitat']."&nbsp;</option>";
			$qq = "SELECT DISTINCT hab.ParentID,hab.Habitat,hab.HabitatID,hab.PathName FROM Habitat as hab WHERE hab.ParentID='".$vvv."' ORDER BY hab.PathName";
			$res = mysql_query($qq,$conn);
			$nrr = mysql_numrows($res);
			if ($nrr>0) {
				while ($rew = mysql_fetch_assoc($res)) {
					$hb = $rew['HabitatID'];
					echo "
<option value='".$hb."'>&nbsp;&nbsp;&nbsp;&nbsp;".$rew['Habitat']."&nbsp;</option>";
					}
				}	
			}
		}
	}
}
echo "
</select>
</td>
</tr>
<tr>
<td style='font-size: 0.6em' align='left'>
* Selecione 1 ou mais habitats
</td>
</tr>
</table>";

?>