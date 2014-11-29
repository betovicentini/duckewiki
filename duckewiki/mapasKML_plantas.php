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

$plantas =1;
//INCLUDE ALL INFO ABOUT RECORD
$formnotes = 0;

$qq = "SELECT 
pltb.PlantaID AS WikiPlantaID,
plantatag(pltb.PlantaID) as TAG_PlantaMarcada,
acentosPorHTML(gettaxonname(pltb.DetID,1,0)) as NOME,
famtb.Familia as FAMILY,
acentosPorHTML(gettaxonname(pltb.DetID,1,1)) as NOME_AUTOR,
localidadestring(pltb.GazetteerID,pltb.GPSPointID,0,0,0,pltb.Latitude,pltb.Longitude,pltb.Altitude) as LOCALIDADE,
(IF(pltb.GPSPointID>0,countrygps.Country,IF(pltb.GazetteerID>0,countrygaz.Country,' '))) as PAIS, 
(IF(pltb.GPSPointID>0,provigps.Province,IF(pltb.GazetteerID>0,provgaz.Province,' '))) as ESTADO, 
(IF(pltb.GPSPointID>0,munigps.Municipio,IF(pltb.GazetteerID>0,muni.Municipio,' '))) as MUNICIPIO, 
(IF(pltb.GPSPointID>0,gazgps.PathName,IF(pltb.GazetteerID>0,gaz.PathName,' '))) as LOCAL,
(IF(pltb.GPSPointID>0,gazgps.Gazetteer,IF(pltb.GazetteerID>0,gaz.Gazetteer,' '))) as LOCALSIMPLES,
getlatlong(pltb.Latitude , pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, muni.MunicipioID, 0, 0, 0) as LONGITUDE,
getlatlong(pltb.Latitude , pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, muni.MunicipioID, 0, 0, 1) as LATITUDE,
IF(ABS(pltb.Longitude)>0,pltb.Altitude+0,IF(pltb.GPSPointID>0,gpspt.Altitude+0,IF(ABS(gaz.Longitude)>0,gaz.Altitude+0,NULL))) as ALTITUDE,
pltb.HabitatID as HabitatID,
projetostring(pltb.ProjetoID,1,0) as PROJETO,
projetologo(pltb.ProjetoID) as PROJETOlogofile,
labeldescricao(0,pltb.PlantaID+0,".$formnotes.",FALSE,FALSE) as NOTAS";
if ($daptraitid>0) {
	$qq .= ",
(traitvalueplantas(".$daptraitid.", pltb.PlantaID, 'mm', 0, 1)+0) AS DAPmm";
}
if ($alturatraitid>0) {
	$qq .= ",
(traitvalueplantas(".$alturatraitid.", pltb.PlantaID, 'm', 0, 1))+0 AS ALTURA";

}
if ($habitotraitid>0) {
	$qq .= ",
(traitvalueplantas(".$habitotraitid.", pltb.PlantaID, '', 0, 0)) AS HABITO";
}

$qq .= " FROM Plantas as pltb 
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
LEFT JOIN Province as provigps ON munigps.ProvinceID=provigps.ProvinceID  
LEFT JOIN Country  as countrygps ON provigps.CountryID=countrygps.CountryID
";
$qq .= " WHERE pltb.PlantaID='".$plantaid."'";

//echo $qq."<br >";
$res = mysql_query($qq,$conn);

$export_filename = "temp_mapplanta_".$plantaid."_".$uuid.".kml";

$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);
$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");
$hh = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<kml xmlns=\"http://www.opengis.net/kml/2.2\">
<Document>
  <name>".$_SESSION['userlastname']."_".$_SESSION['sessiondate']."</name>
  <description>INSTITUTO NACIONAL DE PESQUISAS DA AMAZÔNIA (INPA), Manaus, Brasil. \nArquivo gerado por ".$url." ".$_SESSION['userfirstname']." ".$_SESSION['userlastname']." em ".$_SESSION['sessiondate']."</description>
  ";
fwrite($fh, $hh);
while ($rsw = mysql_fetch_assoc($res)){
			$quq = "SELECT TraitVariation FROM Traits_variation as trv JOIN Traits as tr USING(TraitID) WHERE tr.TraitTipo='Variavel|Imagem' AND trv.PlantaID=".$rsw['WikiPlantaID']." ORDER BY tr.TraitName";
			$ruq = mysql_query($quq,$conn);
			$nruq = mysql_numrows($ruq);
			$ll = $rsw['LONGITUDE']+0;
			if (abs($ll)>0) {

			if (!isset($lati)) {
				$lati = $rsw['LATITUDE'];
			} else {
				$lati = $lati.";".$rsw['LATITUDE'];
			}
			if (!isset($llong)) {
				$llong = $rsw['LONGITUDE'];
			} else {
				$llong = $llong.";".$rsw['LONGITUDE'];
			}
			$onome = trim($rsw['NOME']);
$txt = '';
$newfam = trim($rsw['FAMILY']);
if ($pfam!=$newfam && $ffidx>0) {
		$txt .= "
</Folder>
</Folder>";
$nff = 1;
} else {
$nff = 0;
}
if ($pfam!=$newfam) {
	$txt .= "
<Folder>
<name>".$rsw['FAMILY']."</name>
  <open>0</open>";
	$pfam = $newfam;
}
$ffidx++;

if ($rnspp!=$onome) {
	if ($idzxx>0 && $nff==0) {
		$txt .= "
</Folder>";
	} 
	$txt .= "
<Folder>
<name>".$rsw['NOME']."</name>
  <open>0</open>";
	$idzxx++;
	$rnspp = $onome;
}
if ($nruq>0) {
$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);
$url2 = $url."/icons/images.jpg";
}

$tbwidth = 300;
$titlefont = "1em";
$txtfont = "0.8em";

//$idd = RemoveAcentos(substr($rsw['COLLECTOR'],0,3)."\n".$rsw['NUMBER']);
$txt .= 
"
<Placemark>
  <name>".$rsw['TAG_PlantaMarcada']."</name>
  <visibility>1</visibility>
  <classe>".$rsw['NOME']."</classe>
  <identif>".$rsw['TAG_PlantaMarcada']."</identif>
  <country>".$rsw['PAIS']."</country>
  <prov>".$rsw['ESTADO']."</prov>
  <muni>".$rsw['MUNICIPIO']."</muni>
  <gazz>".$rsw['LOCALIDADE']."</gazz>
  <description>
<![CDATA[
<table width='".$tbwidth."'>
<tr style='font-size: ".$titlefont."'><td>".strtoupper($rsw['FAMILY'])."</td></tr>
<tr style='font-size: ".$titlefont."'><td>".$rsw['NOME_AUTOR']."</td></tr>
";
if (!empty($rsw['detdetby'])) {
$txt .= "
<tr style='font-size: ".$txtfont."'><td>Identificado por ".$rsw['detdetby']."</td></tr>";
}
$dethist = returnDEThistoryAStable($rsw['WikiPlantaID'],0,$conn);
if (count($dethist)>0) {
$txt .= "
<tr><td><hr></td></tr>
<tr style='font-size: ".$txtfont."'><td><b>Histórico das identificações</b></td></tr>
";
foreach ($dethist as $detvv) {
	$txt .= "<tr style='font-size: ".$txtfont."'><td>".$detvv."</td></tr>";
}
}
if (!empty($rsw['NOME_VULGAR'])) {
$txt .= "
<tr style='font-size: ".$txtfont."'><td>Nome vulgar: ".$rsw['NOME_VULGAR']."</td></tr>";
}
$txt .= "
<tr><td><hr></td></tr>
<tr style='font-size: ".$titlefont."'><td><b>Planta No. ".$rsw['TAG_PlantaMarcada']." </b></td></tr>
<tr style='font-size: ".$titlefont."'><td>[".$rsw['LOCALSIMPLES']." ]</td></tr>
";
$txt .= "
<tr><td><hr></td></tr>
<tr style='font-size: ".$txtfont."'><td>".$rsw['LOCALIDADE']."</td></tr>";
if ($rsw['HabitatID']>0) {
$habitat = describehabitat($rsw['HabitatID'],$img=FALSE,$conn);
$txt .= "
<tr><td><hr></td></tr>
<tr style='font-size: ".$txtfont."'><td>".$habitat."</td></tr>";
}


if (!empty($rsw['DAPmm']) || !empty($rsw['ALTURA']) || !empty($rsw['HABITO'])) {
	$txt .= "
<tr><td><hr></td></tr>";
if (!empty($rsw['HABITO'])) {
	$txt .= "
<tr style='font-size: ".$titlefont."'><td><b>HABITO</b>:".$rsw['HABITO']."</td></tr>
";
}
if (!empty($rsw['DAPmm'])) {
	$txt .= "
<tr style='font-size: ".$titlefont."'><td><b>DAPmm</b>:".$rsw['DAPmm']."</td></tr>";
}
if (!empty($rsw['ALTURA'])) {
	$txt .= "
<tr style='font-size: ".$titlefont."'><td><b>ALTURA</b>:".$rsw['ALTURA']." m</td></tr>
";
}
}
if (!empty($rsw['NOTAS'])) {
	$txt .= "
<tr><td><hr></td></tr>
<tr style='font-size: ".$titlefont."'><td><b>Notas</b>:".$rsw['NOTAS']."</td></tr>";
}
$txt .= "
<tr ><td><hr></td></tr>
<tr>
<td>
<table style='border: 0;' align='center' cellpadding='3'>
<tr ><td><img src=\"".$url."/icons/inpa_gov.png\" width='150' /></td></tr>";
if (!empty($rsw['PROJETO'])) {
	$txt .= "
<tr style='font-size: ".$txtfont."'><td>".$rsw['PROJETO']."</td></tr>";
}
$txt .= "
</table>
</td>
</tr>
";

//imagens
$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);
$urlbig = $url."/img/originais/";
$urllowres = $url."/img/lowres/";

if ($nruq>0) {
$txt .= "
<tr ><td><hr></td></tr>
<tr><td>
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
<tr><td><a href=\"".$urlbig.$rusqw['FileName']."\"><img src=\"".$urllowres.$rusqw['FileName']."\" width='200'></a><br></td></tr>";
		$txt .= $tutx;
	}
}
$txt .= "
</table>
<td>
</tr>
";
}
$txt .= "
</table>
]]>
  </description>
  <nimgs>".$nruq."</nimgs>
  <Point>
<coordinates>".$rsw['LONGITUDE'].",".$rsw['LATITUDE'].",".$rsw['ALTITUDE']."</coordinates>
  </Point>
</Placemark>";
	fwrite($fh, $txt);
	}
}

$txt = "
</Folder>
</Folder>
</Document>
</kml>";
fwrite($fh,$txt);
fclose($fh);



$ll = explode(";",$lati);
$llo = explode(";",$llong);

if (count($llo)>0) {

$yy = array_sum($ll)/count($ll);
$xx = array_sum($llo)/count($llo);

//imagens
$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);
$fn = "Location: mapasKML_download.php?export_filename=".$export_filename;
if ($download==1) {
header($fn);
} else {
echo "
<form name='sendform' action='mapasJsonShow.php' method='post'>  
<input type='hidden' value='".$yy."'  name='latcenter'>   
<input type='hidden' value='".$xx."'  name='longcenter'>   
<input type='hidden' value='".$export_filename."'  name='filename'>   
</form>";
echo "<script language=\"JavaScript\">setTimeout('document.sendform.submit()',0.00001);</script>";
}
} else {
echo "<p style='font-size: 1.5em;'>Não há informação geográfica para os dados selecionados</p>";

}

?>