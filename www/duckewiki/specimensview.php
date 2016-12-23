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
$qq = "DROP TABLE ".$tbname3;
mysql_query($qq,$conn);
$qq = "CREATE TABLE ".$tbname3." (SELECT 
pltb.EspecimenID AS WikiSpecimenID,
famtb.Familia as Familia, 
IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as NOME,
acentosPorHTML(CONCAT(colpessoa.SobreNome,'_',pltb.Number)) as ColetorNumero,
acentosPorHTML(colpessoa.Abreviacao) as Coletor,
pltb.Number as Numero, 
CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day) as DataColeta,
INPA_ID as INPA,
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
acentosPorHTML(IF(pltb.GPSPointID>0,countrygps.Country,IF(pltb.GazetteerID>0,countrygaz.Country,' '))) as COUNTRY, 
acentosPorHTML(IF(pltb.GPSPointID>0,provigps.Province,IF(pltb.GazetteerID>0,provgaz.Province,' '))) as MAJORAREA, 
acentosPorHTML(IF(pltb.GPSPointID>0,munigps.Municipio,IF(pltb.GazetteerID>0,muni.Municipio,' '))) as MINORAREA, 
acentosPorHTML(IF(pltb.GPSPointID>0,gazgps.PathName,IF(pltb.GazetteerID>0,gaz.PathName,' '))) as GAZETTEER, 
acentosPorHTML(IF(pltb.GPSPointID>0,pltb.GPSPointID,'')) as GPSpointID,
acentosPorHTML(IF(pltb.GPSPointID>0,gazgps.Gazetteer,IF(pltb.GazetteerID>0,gaz.Gazetteer,''))) as GAZETTEER2,
IF(ABS(pltb.Longitude)>0,pltb.Longitude,IF(pltb.GPSPointID>0,gpspt.Longitude,IF(gaz.Longitude<>0,gaz.Longitude,muni.Longitude))) as LONGITUDE, 
IF(ABS(pltb.Longitude)>0,pltb.Latitude,IF(pltb.GPSPointID>0,gpspt.Latitude,IF(gaz.Longitude<>0,gaz.Latitude,muni.Latitude))) as LATITUDE, 
IF(ABS(pltb.Longitude)>0,pltb.Altitude,IF(pltb.GPSPointID>0,gpspt.Altitude,IF(gaz.Longitude<>0,gaz.Altitude,''))) as ALTITUDE, 
acentosPorHTML(IF(ABS(pltb.Longitude)>0,'GPS-planta',IF(pltb.GPSPointID>0,'GPS',IF(gaz.Longitude<>0,'Localidade','Municipio')))) as COORD_PRECISION,
SUBSTR(acentosPorHTML(notastring(pltb.EspecimenID, ".$formid.",TRUE,'Especimenes')),1,230) as NOTAS,
acentosPorHTML(habitatstring(pltb.HabitatID, 5, TRUE,FALSE)) as habitat,"; 
//acentosPorHTML(IF(pltb.GPSPointID>0,CONCAT(gazgps.GazetteerTIPOtxt,' ',gazgps.Gazetteer),IF(pltb.GazetteerID>0,CONCAT(gaz.GazetteerTIPOtxt,' ',gaz.Gazetteer),''))) as GAZETTEER2,
$qq .= " acentosPorHTML(vernaculars(pltb.VernacularIDS)) as Vernaculars, 
acentosPorHTML(projetostring(pltb.ProjetoID,TRUE,0)) as Projeto 
FROM ".$tbname2." as filtertab JOIN Especimenes as pltb USING(EspecimenID) 
LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID
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
WHERE filtertab.EspecimenID>0 AND filtertab.Ntimes= filtertab.NCriteria)";
$rr = mysql_query($qq,$conn);

//echo $qq."<br>";
if ($rr) {
$qq = "SHOW Fields FROM ".$tbname3;
$rr = mysql_query($qq,$conn);
$nrr = mysql_numrows($rr);

if ($nrr>0) {
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
$qq = "SELECT * FROM ".$tbname3." PROCEDURE ANALYSE()";
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
$fnn = $tbname3.".php";
$fh = fopen("temp/".$fnn, 'w');
$stringData = "<?php
require_once(\"../dhtmlxconnector/dhtmlxConnector_php/codebase/grid_connector.php\");
include(\"../../../includes/".$dbname."_clean.php\");
\$grid = new GridConnector(\$res);
\$grid ->dynamic_loading(100);
\$grid->render_table(\"".$tbname3."\",\"WikiSpecimenID\",\"".$hdd."\")
?>";
fwrite($fh, $stringData);
fclose($fh);

$qq = "SELECT COUNT(*) AS nrecs FROM ".$tbname3;
$rr = mysql_query($qq,$conn);
$rw = mysql_fetch_assoc($rr);
$nrecs = $rw['nrecs'];


//echo "<br>".$stringData;
echo "
  <form name='myform' action='specimensview2.php' method='post'>
  <input type='hidden' value=$tbname3  name='tbname'>
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

} else {
	echo "nenhum registro";	

}

}
HTMLtrailers();

?>