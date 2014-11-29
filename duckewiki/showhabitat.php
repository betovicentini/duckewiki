<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//CHECA SE O USUARIO TEM PERMISSAO
$uuid = cleanQuery($_SESSION['userid'],$conn);
if((!isset($uuid) || 
	(trim($uuid)=='')) && count($listsarepublic)==0) {
		header("location: access-denied.php");
	exit();
} else {
	$acclevel = $_SESSION['accesslevel'];
}

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

//CABECALHO
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Listar espécies';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$formnotes = 0;
if ($especimenid>0 || $plantaid>0) {
if ($especimenid>0) {
$qq .= " SELECT 
	pltb.EspecimenID AS WikiEspecimenID, 
	localidadestring(pltb.GazetteerID,pltb.GPSPointID,pltb.MunicipioID,pltb.ProvinceID,pltb.CountryID,IF(ABS(pltb.Longitude)>0,pltb.Latitude,IF(pltb.GPSPointID>0,gpspt.Latitude,IF(ABS(gaz.Longitude)>0,gaz.Latitude,muni.Latitude))),IF(ABS(pltb.Longitude)>0,pltb.Longitude+0,IF(pltb.GPSPointID>0,gpspt.Longitude+0,IF(ABS(gaz.Longitude)>0,gaz.Longitude+0,NULL))),IF(ABS(pltb.Longitude)>0,pltb.Altitude,IF(pltb.GPSPointID>0,gpspt.Altitude,IF(ABS(gaz.Longitude)>0,gaz.Altitude,'')))) as LOCALIDADE,
	IF(pltb.HabitatID>0,pltb.HabitatID,IF(plspectb.HabitatID>0,plspectb.HabitatID,0)) as HabitatID,
	projetostring(pltb.ProjetoID,1,0) as PROJETO,
	projetologo(pltb.ProjetoID) as PROJETOlogofile
	FROM Especimenes as pltb 
	LEFT JOIN Plantas as plspectb ON plspectb.PlantaID=pltb.PlantaID  
	LEFT JOIN Gazetteer as gaz ON gaz.GazetteerID=pltb.GazetteerID  
	LEFT JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID  
	LEFT JOIN Province as provgaz ON muni.ProvinceID=provgaz.ProvinceID
	LEFT JOIN Country  as countrygaz ON provgaz.CountryID=countrygaz.CountryID  
	LEFT JOIN GPS_DATA as gpspt ON gpspt.PointID=pltb.GPSPointID  
	LEFT JOIN Gazetteer as gazgps ON gpspt.GazetteerID=gazgps.GazetteerID  
	LEFT JOIN Municipio as munigps ON gazgps.MunicipioID=munigps.MunicipioID 
	LEFT JOIN Province as provigps ON munigps.ProvinceID=provigps.ProvinceID  
	LEFT JOIN Country  as countrygps ON provigps.CountryID=countrygps.CountryID";
	$qq .= " WHERE pltb.EspecimenID='".$especimenid."'";
}
if ($plantaid>0) {
$qq .= " SELECT 
	pltb.PlantaID AS WikiPlantaID, 
localidadestring(pltb.GazetteerID,pltb.GPSPointID,0,0,0,IF(ABS(pltb.Longitude)>0,pltb.Latitude,IF(pltb.GPSPointID>0,gpspt.Latitude,IF(ABS(gaz.Longitude)>0,gaz.Latitude,muni.Latitude))),IF(ABS(pltb.Longitude)>0,pltb.Longitude+0,IF(pltb.GPSPointID>0,gpspt.Longitude+0,IF(ABS(gaz.Longitude)>0,gaz.Longitude+0,NULL))),IF(ABS(pltb.Longitude)>0,pltb.Altitude,IF(pltb.GPSPointID>0,gpspt.Altitude,IF(ABS(gaz.Longitude)>0,gaz.Altitude,'')))) as LOCALIDADE,
	IF(pltb.HabitatID>0,pltb.HabitatID,0) as HabitatID,
	projetostring(pltb.ProjetoID,1,0) as PROJETO,
	projetologo(pltb.ProjetoID) as PROJETOlogofile
	FROM Plantas as pltb 
	LEFT JOIN Gazetteer as gaz ON gaz.GazetteerID=pltb.GazetteerID  
	LEFT JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID  
	LEFT JOIN Province as provgaz ON muni.ProvinceID=provgaz.ProvinceID
	LEFT JOIN Country  as countrygaz ON provgaz.CountryID=countrygaz.CountryID  
	LEFT JOIN GPS_DATA as gpspt ON gpspt.PointID=pltb.GPSPointID  
	LEFT JOIN Gazetteer as gazgps ON gpspt.GazetteerID=gazgps.GazetteerID  
	LEFT JOIN Municipio as munigps ON gazgps.MunicipioID=munigps.MunicipioID 
	LEFT JOIN Province as provigps ON munigps.ProvinceID=provigps.ProvinceID  
	LEFT JOIN Country  as countrygps ON provigps.CountryID=countrygps.CountryID";
	$qq .= " WHERE pltb.PlantaID='".$plantaid."'";
}


	$qqq = $qq;
	$res = mysql_query($qqq,$conn);
	$txt = '';
	$rsw = mysql_fetch_assoc($res);

	if ($rsw['HabitatID']>0) {
		$quq = "SELECT TraitVariation FROM Habitat_Variation as trv JOIN Traits as tr USING(TraitID) WHERE tr.TraitTipo='Variavel|Imagem' AND trv.HabitatID=".$rsw['HabitatID']." ORDER BY tr.TraitName";
		$ruq = @mysql_query($quq,$conn);
		$nruq = @mysql_numrows($ruq);
	} else {
		$nruq = 0;
	}

$txt ='';
//imagens
$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);

$txt .= "
<hr>
<font size=2>".$rsw['LOCALIDADE']."</font>";
if ($rsw['HabitatID']>0) {
$habitat = describehabitat($rsw['HabitatID'],$img=FALSE,$conn);
$txt .= "
<hr>
<font size=2>".$habitat."</font>";
}
if (!empty($rsw['NOTAS'])) {
	$txt .= "
<hr>
<font size=2><b>Notas</b>:".$rsw['NOTAS']."</font>";
}

//PROJETO
$urllogo = $url."/";
if ($rsw['PROJETOlogofile']!='') {
	$img = $urllogo.$rsw['PROJETOlogofile'];
} else {
	if (!empty($herbariumlogo)) {
	$img = $urllogo."icons/".$herbariumlogo;
	} else {
		$img ='';
	}
}
$txt .= "
<hr>
<table style='border: 0;' align='center' cellpadding='3'>
<tr><td>
<img src=\"".$img."\" height='50'></td>";
if (!empty($rsw['PROJETO'])) {
	$txt .= "
<td><font size=2>".$rsw['PROJETO']."</font></td>";
}
$txt .= "
</tr></table>";

//IMAGENS
$urlbig = $url."/img/originais/";
$url = $url."/img/lowres/";
if ($nruq>0) {
$txt .= "
<hr>
<table style='border: 0;' align='center' cellpadding='10'>";

while ($ruqw = mysql_fetch_assoc($ruq)) {
	$imgs = explode(";",$ruqw['TraitVariation']);
	foreach ($imgs as $vimg) {
		$vimg = $vimg+0;
		$qusq = "SELECT FileName FROM Imagens WHERE ImageID='".$vimg."'";
		//echo $qusq;
		$rusq = mysql_query($qusq,$conn);
		$rusqw = mysql_fetch_assoc($rusq);
		$tutx = "
<tr><td><a href=\"".$urlbig.$rusqw['FileName']."\"><img src=\"".$url.$rusqw['FileName']."\" width=300></a><br /></td></tr>";
		$txt .= $tutx;
	}
}
$txt .= "
</table>
<hr>";
}
echo "<div style='width: 400px;' >$txt</div>";
} 

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>