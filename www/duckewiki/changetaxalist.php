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

if (!empty($onme)) {
	$vals = explode("_",$onme);
	if ($vals[0]=='genid') {
		$filtro = ' iddet.GeneroID='.$vals[1].' AND iddet.EspecieID>0';
	} else {
		$filtro = ' iddet.FamiliaID='.$vals[1].' AND iddet.EspecieID>0';
	}
}
//echo $filtro;
//echopre($gget);
//$qq = "SELECT DISTINCT iddet.EspecieID,iddet.InfraEspecieID,IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',spectb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',spectb.Especie),'')) as NOME FROM Plantas as pltb LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID LEFT JOIN Tax_Especies as spectb ON iddet.EspecieID=spectb.EspecieID LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID WHERE pltb.GazetteerID=".$gazetteerid." checkgazetteerchilds(".$gazetteerid.", 'pltb.GazetteerID') AND ".$filtro."  ORDER BY famtb.Familia,gentb.Genero,spectb.Especie,infsptb.InfraEspecie";
//echo $qq."<br>";

$qq = "SELECT DISTINCT iddet.EspecieID,iddet.InfraEspecieID,IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',spectb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',spectb.Especie),'')) as NOME FROM Plantas as pltb LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID LEFT JOIN Tax_Especies as spectb ON iddet.EspecieID=spectb.EspecieID LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID WHERE (pltb.GazetteerID=".$gazetteerid;
$qu = "SELECT * FROM Gazetteer WHERE ParentID='".$gazetteerid."'";
$rzz = mysql_query($qu,$conn);
$nrzz = mysql_numrows($rzz);
if ($nrzz>0) {
	while ($row = mysql_fetch_assoc($rzz)) {
		$qq .= " OR pltb.GazetteerID='".$row['GazetteerID']."'";
	}
}
$qq .= ") AND ".$filtro."  ORDER BY famtb.Familia,gentb.Genero,spectb.Especie,infsptb.InfraEspecie";
//echo $qq."<br>";
$rz = mysql_query($qq,$conn);
$nrz = mysql_numrows($rz);
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
<select id=\"specieslist\" name='speciestoplot[]' size='".$selsize."' multiple >";
if ($nrz==0) {
echo "
<option selected value=''>Nenhuma especie encontrada</option>";
} else {
	while ($row = mysql_fetch_assoc($rz)) {
		if ($stspecid=="specid_".$row['EspecieID']) {
			$txtsel = "selected";
		} else {
			$txtsel = "";
		}
	
		if ($row['InfraEspecieID']>0) {
			$vv = 'infspid_'.$row['InfraEspecieID'];
		} else {
			$vv = 'specid_'.$row['EspecieID'];
		}
			echo "
<option $txtsel value='".$vv."'>".$row['NOME']."&nbsp;</option>";
	}
}
echo "
</select>
</td>
</tr>
<tr>
<td style='font-size: 0.6em' align='left'>
* Selecione 1 ou mais espécies
</td>
</tr>
</table>";

?>