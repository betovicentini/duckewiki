<?php

set_time_limit(0);

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

$tbname = "tempfiltro_".session_id();
$formid = 13;

HTMLheaders($body);
$qq = "DROP TABLE ".$tbname4;
mysql_query($qq,$conn);
$qq = "CREATE TABLE ".$tbname4." (SELECT pltb.PlantaID AS WikiPlantaID,
famtb.Familia as Familia, 
IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as NOME,
IF((pltb.InSituExSitu='' OR pltb.InSituExSitu IS NULL),pltb.PlantaTag,IF(pltb.InSituExSitu LIKE 'Insitu',CONCAT('JB-X-',pltb.PlantaTag),CONCAT('JB-N-',pltb.PlantaTag))) as TAG_NUM,
gentb.Genero as GENUS, 
sptb.Especie as SP1, 
acentosPorHTML(sptb.EspecieAutor) as AUTHOR1, 
infsptb.InfraEspecieNivel as RANK1, 
infsptb.InfraEspecie as SP2, 
acentosPorHTML(infsptb.InfraEspecieAutor) as AUTHOR2, 
iddet.DetModifier as CF, 
acentosPorHTML(detpessoa.Abreviacao) as DETBY, 
DAY(iddet.DetDate) as DETDD, 
MONTH(iddet.DetDate) as DETMM, 
YEAR(iddet.DetDate) as DETYY,  
acentosPorHTML(CONCAT(detpessoa.Abreviacao,' [',DATE_FORMAT(iddet.DetDate,'%d-%b-%Y'),']')) as DETERMINACAO,	
acentosPorHTML(IF(pltb.GazetteerID>0,countrygaz.Country,countrygps.Country)) as COUNTRY,
acentosPorHTML(IF(pltb.GazetteerID>0,provgaz.Province,provigps.Province)) as MAJORAREA,
acentosPorHTML(IF(pltb.GazetteerID>0,muni.Municipio,munigps.Municipio)) as MINORAREA,
acentosPorHTML(IF(pltb.GPSPointID>0,gazgps.PathName,IF(pltb.GazetteerID>0,gaz.PathName,''))) as GAZETTEER, 
acentosPorHTML(IF(pltb.GPSPointID>0,TRIM(gazgps.Gazetteer),IF(pltb.GazetteerID>0,TRIM(gaz.Gazetteer),''))) as GAZETTEER2, 
IF(ABS(pltb.Longitude)>0,pltb.Longitude,IF(pltb.GPSPointID>0,gpspt.Longitude,IF(gaz.Longitude<>0,gaz.Longitude,muni.Longitude))) as LONGITUDE, 
IF(ABS(pltb.Longitude)>0,pltb.Latitude,IF(pltb.GPSPointID>0,gpspt.Latitude,IF(gaz.Longitude<>0,gaz.Latitude,muni.Latitude))) as LATITUDE, 
IF(ABS(pltb.Longitude)>0,pltb.Altitude,IF(pltb.GPSPointID>0,gpspt.Altitude,IF(gaz.Longitude<>0,gaz.Altitude,''))) as ALTITUDE, 
acentosPorHTML(IF(ABS(pltb.Longitude)>0,'GPS-planta',IF(pltb.GPSPointID>0,'GPS',IF(gaz.Longitude<>0,'Localidade','Municipio')))) as COORD_PRECISION,  
pltb.X as Pos_X, 
pltb.Y as Pos_Y, 
pltb.LADO as Pos_LADO, 
pltb.Referencia as Pos_REF, 
pltb.Distancia as Pos_DIST, 
pltb.Angulo as Pos_ANGULO,";
//acentosPorHTML(IF(pltb.GPSPointID>0,TRIM(CONCAT(gazgps.GazetteerTIPOtxt,' ',gazgps.Gazetteer)),IF(pltb.GazetteerID>0,TRIM(CONCAT(gaz.GazetteerTIPOtxt,' ',gaz.Gazetteer)),''))) as GAZETTEER2, 
//acentosPorHTML(notastring(pltb.PlantaID,'".$formid."',TRUE,'Plantas'))  as NOTAS,
//acentosPorHTML(habitatstring(pltb.HabitatID, 5, TRUE,FALSE)) as habitat,
$qq .= "acentosPorHTML(vernaculars(pltb.VernacularIDS)) as NOME_VULGAR, 
acentosPorHTML(addcolldescr(pltb.TaggedBy)) as MARCADO_POR, 
pltb.TaggedDate as DATA_MARCACAO, 
acentosPorHTML(projetostring(pltb.ProjetoID,1,0)) as PROJETO
FROM ".$tbname2." as filtertab JOIN Plantas as pltb USING(PlantaID) 
LEFT JOIN Identidade as iddet USING(DetID) 
LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID 
LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID 
LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  
LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID 
LEFT JOIN Pessoas as detpessoa ON detpessoa.PessoaID=iddet.DetbyID
LEFT JOIN Gazetteer as gaz ON gaz.GazetteerID=pltb.GazetteerID  
LEFT JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID  
LEFT JOIN Province as provgaz ON muni.ProvinceID=provgaz.ProvinceID  
LEFT JOIN Country  as countrygaz ON provgaz.CountryID=countrygaz.CountryID  
LEFT JOIN GPS_DATA as gpspt ON gpspt.PointID=pltb.GPSPointID  
LEFT JOIN Gazetteer as gazgps ON gpspt.GazetteerID=gazgps.GazetteerID  
LEFT JOIN Municipio as munigps ON gazgps.MunicipioID=munigps.MunicipioID 
LEFT  JOIN Province as provigps ON munigps.ProvinceID=provigps.ProvinceID  
LEFT JOIN Country  as countrygps ON provigps.CountryID=countrygps.CountryID
LEFT JOIN Projetos ON pltb.ProjetoID=Projetos.ProjetoID 
WHERE filtertab.PlantaID>0 AND filtertab.Ntimes= filtertab.NCriteria)";
$rr = mysql_query($qq,$conn);

if ($rr) {
$qq = "SHOW Fields FROM ".$tbname4;
$rr = mysql_query($qq,$conn);
$nrr = mysql_numrows($rr);
$i =0;
$filt = array();
$filt2 = array();
$collist = array();
$coltipos = array();
$ito= $nrr;
$inot = array(0);
while ($row = mysql_fetch_assoc($rr)) {
	if ($i>0 && $i<=$ito && !in_array($i,$inot)) {
	$headd[]  = $row['Field'];
	$filt[] = "#connector_text_filter";
	$filt2[] = "connector";
	$coltipos[] = "rotxt";
	$collist[] = $i-1;
	}
	$i++;
}

$colw = array();
$i =0;
$qq = "SELECT * FROM ".$tbname4." PROCEDURE ANALYSE()";
$rr = mysql_query($qq,$conn);
while ($row = mysql_fetch_assoc($rr)) {
	if ($i>0 && $i<=$ito && !in_array($i,$inot)) {
		if (($row['Max_length']+80)>200) {
			$clw = 200;
		} else {
			$clw = $row['Max_length']+80;
		}
		$colw[] = $clw;
	}
	$i++;
}

//unset($headd[0]);
$hdd = implode(",",$headd);
$ffilt = implode(",",$filt);
$ffilt2 = implode(",",$filt2);
$collist = implode(",",$collist);
$colw = implode(",",$colw);
$coltipos = implode(",",$coltipos);
//echo $hdd;
$fnn = $tbname4.".php";
$fh = fopen("temp/".$fnn, 'w');
$stringData = "<?php
require_once(\"../dhtmlxconnector/dhtmlxConnector_php/codebase/grid_connector.php\");
include(\"../../../includes/".$dbname."_clean.php\");
\$grid = new GridConnector(\$res);
\$grid ->dynamic_loading(100);
\$grid->render_table(\"".$tbname4."\",\"WikiPlantaID\",\"".$hdd."\")
?>";
fwrite($fh, $stringData);
fclose($fh);

$qq = "SELECT COUNT(*) AS nrecs FROM ".$tbname4;
$rr = mysql_query($qq,$conn);
$rw = mysql_fetch_assoc($rr);
$nrecs = $rw['nrecs'];


//echo "<br>".$stringData;
echo "
  <form name='myform' action='plantasview2.php' method='post'>
  <input type='hidden' value=$tbname4  name='tbname'>
  <input type='hidden' value=$fnn  name='fname'>
  <input type='hidden' value=\"".$hdd."\"  name='ffields'>
  <input type='hidden' value=\"".$ffilt."\"  name='filtros'>
  <input type='hidden' value=\"".$ffilt2."\"  name='filtros2'>
  <input type='hidden' value=\"".$collist."\"  name='collist'>
  <input type='hidden' value=\"".$nrecs."\"  name='nrecs'>
  <input type='hidden' value=\"".$colw."\"  name='colw'>
  <input type='hidden' value=\"".$coltipos."\"  name='coltipos'>

  ";
echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script></form>";	

}
HTMLtrailers();

?>