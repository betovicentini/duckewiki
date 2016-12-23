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

//////variaveis deste formulario
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

//$filtro = 113;

//echo $qq."<br>";
if ($filtro>0) {
	$tbname = "temp_".substr(session_id(),0,10);
	$qq = "DROP TABLE ".$tbname;
	@mysql_query($qq,$conn);
} else {
	$tbname = "temp_speclist";
}
HTMLheaders($body);

$qq = "SELECT COUNT(*) FROM $tbname";
$rr = @mysql_query($qq);
$nr = @mysql_numrows($rr);

$formnotes = 60;
$exsicatatrait = 350;
$duplicatesTraitID =  496;
$formidhabitat = 5;
//$update=1;
if ($nr==0 || $update>0) {
	$qq = "DROP TABLE ".$tbname;
	@mysql_query($qq,$conn);
$qq = "
CREATE TABLE IF NOT EXISTS ".$tbname." (SELECT 
pltb.EspecimenID, 
pltb.PlantaID, 
pltb.DetID,
acentosPorHTML(colpessoa.Abreviacao) as COLETOR, 
pltb.Number as NUMERO,
if(CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day)<>'0000-00-00',CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day),'FALTA') as DATA,
if(pltb.INPA_ID>0,pltb.INPA_ID+0,NULL) as INPA_N,
famtb.Familia as FAMILIA,
IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as NOME,
acentosPorHTML(IF(pltb.GPSPointID>0,countrygps.Country,IF(pltb.GazetteerID>0,countrygaz.Country,' '))) as PAIS, 
acentosPorHTML(IF(pltb.GPSPointID>0,provigps.Province,IF(pltb.GazetteerID>0,provgaz.Province,' '))) as ESTADO, 
acentosPorHTML(IF(pltb.GPSPointID>0,munigps.Municipio,IF(pltb.GazetteerID>0,muni.Municipio,' '))) as MUNICIPIO, 
acentosPorHTML(IF(pltb.GPSPointID>0,gazgps.PathName,IF(pltb.GazetteerID>0,gaz.PathName,' '))) as LOCAL, 
IF(ABS(pltb.Longitude)>0,pltb.Longitude+0,IF(pltb.GPSPointID>0,gpspt.Longitude+0,IF(ABS(gaz.Longitude)>0,gaz.Longitude+0,NULL))) as LONGITUDE, 
IF(ABS(pltb.Longitude)>0,pltb.Latitude+0,IF(pltb.GPSPointID>0,gpspt.Latitude+0,IF(ABS(gaz.Longitude)>0,gaz.Latitude+0,NULL))) as LATITUDE, 
IF(ABS(pltb.Longitude)>0,pltb.Altitude+0,IF(pltb.GPSPointID>0,gpspt.Altitude+0,IF(ABS(gaz.Longitude)>0,gaz.Altitude+0,NULL))) as ALTITUDE,
'edit-icon.png' AS EDIT,
'mapping.png' AS MAP,
IF(labeldescricao(IF(pltb.PlantaID>0,0,pltb.EspecimenID+0),pltb.PlantaID,".$formnotes.",1,0)<>'','edit-notes.png','question-red.png') as OBS,
IF(pltb.HabitatID>0,'environment_icon.png','question-red.png') as HABT,
IF(projetologo(pltb.ProjetoID)<>'',projetologo(pltb.ProjetoID),'icons/image_missing.png') as PRJ,
acentosPorHTML(IF(projetostring(pltb.ProjetoID,0,0)<>'',projetostring(pltb.ProjetoID,0,0),'NÃO FOI DEFINIDO')) as PROJETO,
if (checkimgs(pltb.EspecimenID, pltb.PlantaID)>0,'camera.png','question-red.png') as IMG,
nduplicates(".$duplicatesTraitID.",pltb.EspecimenID,'Especimenes')+0 as DUPS,
checktrait(pltb.EspecimenID, pltb.PlantaID,".$exsicatatrait.") as EXSICATA_IMG
FROM Especimenes as pltb 
LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID
LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID 
LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID 
LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID 
LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  
LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID 
LEFT JOIN Gazetteer as gaz ON gaz.GazetteerID=pltb.GazetteerID  
LEFT JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID  
LEFT JOIN Province as provgaz ON muni.ProvinceID=provgaz.ProvinceID  
LEFT JOIN Country  as countrygaz ON provgaz.CountryID=countrygaz.CountryID  
LEFT JOIN GPS_DATA as gpspt ON gpspt.PointID=pltb.GPSPointID  
LEFT JOIN Gazetteer as gazgps ON gpspt.GazetteerID=gazgps.GazetteerID  
LEFT JOIN Municipio as munigps ON gazgps.MunicipioID=munigps.MunicipioID 
LEFT  JOIN Province as provigps ON munigps.ProvinceID=provigps.ProvinceID  
LEFT JOIN Country  as countrygps ON provigps.CountryID=countrygps.CountryID";

//echo $qq."<br>";
if ($filtro>0) {
	$qq .= " JOIN FiltrosSpecs as fl ON fl.EspecimenID=pltb.EspecimenID WHERE fl.FiltroID=".$filtro.")";
} else {
	$qq .= ")";
}
$check = mysql_query($qq,$conn);

$qq = "ALTER TABLE ".$tbname." ADD PRIMARY KEY(EspecimenID)";
mysql_query($qq,$conn);

$sql = "CREATE INDEX COLETOR ON ".$tbname."  (COLETOR)";
mysql_query($sql,$conn);
$sql = "CREATE INDEX NUMERO ON ".$tbname."  (NUMERO)";
mysql_query($sql,$conn);
$sql = "CREATE INDEX INPA_NUM ON ".$tbname."  (INPA_N)";
mysql_query($sql,$conn);
$sql = "CREATE INDEX NOME ON ".$tbname."  (NOME)";
mysql_query($sql,$conn);
$sql = "CREATE INDEX PAIS ON ".$tbname."  (PAIS)";
mysql_query($sql,$conn);
$sql = "CREATE INDEX ESTADO ON ".$tbname."  (ESTADO)";
mysql_query($sql,$conn);
$sql = "CREATE INDEX MUNICIPIO ON ".$tbname."  (MUNICIPIO)";
mysql_query($sql,$conn);
$sql = "CREATE INDEX LOCAL ON ".$tbname."  (LOCAL)";
mysql_query($sql,$conn);
}
$headd = array("EDIT","COLETOR", "NUMERO", "DATA", "INPA_N", "FAMILIA", "NOME", "PAIS", "ESTADO", "MUNICIPIO", "LOCAL", "LONGITUDE", "LATITUDE", "ALTITUDE", "DUPS","MAP","OBS","HABT","IMG","PRJ","PROJETO","EXSICATA_IMG");
//$filt2 = array("str", "int", "date", "int", "str", "str", "str", "str", "str", "int", "int", "int", "str", "str", "int","str","str");
$listvisible = $headd;
$filt = $headd;
$filt2 = $headd;
$coltipos = $headd;
$colw = $headd;
$nofilter = array("OBS", "IMG", "PRJ", "EDIT", "HABT","MAP");
$imgfields = array("OBS", "IMG", "PRJ", "EDIT", "HABT","MAP");
$hidefields = array("PROJETO", "EXSICATA_IMG");
$i=1;
$ncl = count($headd)-count($imgfields)-count($hidefields);
$nimg = count($imgfields);
$nimg = $nimg*50;
$cl = floor((900-$nimg)/$ncl);
foreach ($headd as $kk => $vv) {
	$qq = "SELECT * FROM ".$tbname." PROCEDURE ANALYSE() WHERE Field_name LIKE '%".$tbname.".".$vv."%'";
	$rr = @mysql_query($qq,$conn);
	$row = @mysql_fetch_assoc($rr);
	if (($row['Max_length']+80)>200) {
		$clw = 200;
	  } else {
		$clw = $row['Max_length']+80;
	}
	if (!in_array($vv,$nofilter)) {
		$filt[$kk] = "#connector_text_filter";
	} else {
		$filt[$kk] = '';
	}
	if (!in_array($vv,$imgfields)) {
		$coltipos[$kk] = "rotxt";
		$colw[$kk] = $cl;
	} else {
		$coltipos[$kk] = 'ro';
		if ($vv=='OBS') {
			$colw[$kk] = 50;
		} else {
			$colw[$kk] = 50;
		}
	}
	//$row['Max_length']+10
	$filt2[$kk] = "connector";
	if (!in_array($vv,$hidefields)) {
		$listvisible[$kk] = 'false';
	} else {
		$listvisible[$kk] = 'true';
	}
	$collist[] = $i;
	$i++;
}

$hdd = implode(",",$headd);
$ffilt = implode(",",$filt);
$ffilt2 = implode(",",$filt2);
$collist = implode(",",$collist);
$colw = implode(",",$colw);
$coltipos = implode(",",$coltipos);
$listvisible = implode(",",$listvisible);
$fnn = $tbname.".php";

if ($nr==0 || $update>0) {
$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);


$fh = fopen("temp/".$fnn, 'w');
$stringData = "<?php
require_once(\"../dhtmlxconnector/dhtmlxConnector_php/codebase/grid_connector.php\");
include(\"../../../includes/".$dbname."_clean.php\");
function custom_format(\$data){
	if (\$data->get_value(\"IMG\")==\"camera.png\") {
    \$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"IMG\").\"' height='20' onclick=\\\"javascript:small_window('".$url."/image_specimen.php?specimenid=\".\$data->get_value(\"EspecimenID\").\"',600,400,'Ver imagens');\\\" alt='Ver imagens' >\";
    \$data->set_value(\"IMG\",\$imagen);
    } else {
    \$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"IMG\").\"' height='20' onclick=\\\"javascript:alert('Nao ha imagens!');\\\" alt='Ver imagens' >\";
    \$data->set_value(\"IMG\",\$imagen);
    }

	if (\$data->get_value(\"HABT\")==\"environment_icon.png\") {
    \$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"HABT\").\"' height='20' onclick=\\\"javascript:small_window('".$url."/showhabitat.php?especimenid=\".\$data->get_value(\"EspecimenID\").\"',500,400,'Ver imagens');\\\"  alt='Sobre habitat' >\";
    \$data->set_value(\"HABT\",\$imagen);
    } else {
    \$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"HABT\").\"' height='20' onclick=\\\"javascript:alert('Não há informação sobre habitat para esta amostra!');\\\" alt='Sobre habitat' >\";
    \$data->set_value(\"HABT\",\$imagen);
    }
    
    \$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"OBS\").\"' height='20' onclick=\\\"javascript:small_window('".$url."/showspecimen.php?especimenid=\".\$data->get_value(\"EspecimenID\").\"',400,400,'Notas');\\\" alt='Ver Notas' >\";
    \$imgg =\"<img style='cursor:pointer;' src='icons/label-icon.png' height='20' onclick=\\\"javascript:small_window('".$url."/singlelabel-exec.php?specimenid=\".\$data->get_value(\"EspecimenID\").\"',300,100,'Imprimindo Etiqueta');\\\" alt='Etiquetas em PDF' >\";
    \$imagen = \$imagen.\"&nbsp;\".\$imgg;
    \$data->set_value(\"OBS\",\$imagen);

	 \$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"EDIT\").\"' height='20' onclick=\\\"javascript:small_window('".$url."/novacoleta-exec-popup.php?especimenid=\".\$data->get_value(\"EspecimenID\").\"',1000,400,'Notas');\\\" alt='Editar registro' >\";
    \$data->set_value(\"EDIT\",\$imagen);

	\$llat = ABS($data->get_value(\"LATITUDE\"));
	\$llong = ABS(\$data->get_value(\"LONGITUDE\"));
	\$llcord = \$llat+\$llong;
	if (\$llcord>0) {
	 \$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"MAP\").\"' height='18' onclick=\\\"javascript:small_window('".$url."/mapasKML.php?specimenid=\".\$data->get_value(\"EspecimenID\").\"',600,500,'Notas');\\\" alt='Mapear o registro' >\";
	} else {
	 \$imagen=\"<img style='cursor:pointer;' src='icons/question-red.png' height='18' onclick=\\\"javascript:alert('Latitude & Longitude Faltando');\\\" alt='Mapear o registro' >\";
	}
    \$data->set_value(\"MAP\",\$imagen);
    
    \$imagen=\"<img style='cursor:pointer;' src='\".\$data->get_value(\"PRJ\").\"' height='20' onclick=\\\"javascript:alert('\".\$data->get_value(\"PROJETO\").\"');\\\" alt='Sobre o projeto' >\";
    \$data->set_value(\"PRJ\",\$imagen);


}
\$grid = new GridConnector(\$res);
\$grid ->event->attach(\"beforeRender\",\"custom_format\");
\$grid ->dynamic_loading(100);
\$grid ->render_table(\"".$tbname."\",\"EspecimenID\",\"".$hdd."\")
?>";
fwrite($fh, $stringData);
fclose($fh);

}
//\
//
echo "
  <form name='myform' action='checklistview.php' method='post'>
  <input type='hidden' value=$tbname name='tbname'>
  <input type='hidden' value=$fnn  name='fname'>
  <input type='hidden' value=\"".$hdd."\"  name='ffields'>
  <input type='hidden' value=\"".$ffilt."\"  name='filtros'>
  <input type='hidden' value=\"".$ffilt2."\"  name='filtros2'>
  <input type='hidden' value=\"".$collist."\"  name='collist'>
  <input type='hidden' value=\"".$colw."\"  name='colw'>
  <input type='hidden' value=\"".$coltipos."\"  name='coltipos'>
  <input type='hidden' value=\"".$listvisible."\"  name='listvisible'>
  ";
echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script></form>";	




HTMLtrailers();

?>