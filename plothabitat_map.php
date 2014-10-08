<?php
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
//if(!isset($uuid) || 
//	(trim($uuid)=='')) {
//		header("location: access-denied.php");
//	exit();
//} 
$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);

$filename = session_id()."_habitatmap_".$parentid.".kml";
@unlink("temp/".$filename);

$habitats = array();
$qq = " SELECT pltb.HabitatID FROM Habitat as pltb WHERE pltb.ParentID='".$parentid."'";
$rz = mysql_query($qq,$conn);
while ($row = mysql_fetch_assoc($rz)) {
	$habitats[] = $row['HabitatID'];
	$qq = "SELECT hab.HabitatID FROM Habitat as hab WHERE hab.ParentID='".$vv."' ORDER BY hab.PathName";
	$rzz = mysql_query($qq,$conn);
	$nrzz = mysql_numrows($rzz);
	if ($nrzz>0) {
		while ($rw = mysql_fetch_assoc($rzz)) {
			$habitats[]  = $rw['HabitatID'];
			$qq = "SELECT hab.HabitatID FROM Habitat as hab WHERE hab.ParentID='".$vvv."' ORDER BY hab.PathName";
			$res = mysql_query($qq,$conn);
			$nrr = mysql_numrows($res);
			if ($nrr>0) {
				while ($rew = mysql_fetch_assoc($res)) {
					$habitats[] = $rew['HabitatID'];
				}
			}	
		}
	}
}
$habitats = array_unique($habitats);
$qq = " SELECT 
	pltb.HabitatID,
	pltb.PathName,
	localidadestring(pltb.LocalityID,pltb.GPSPointID,0,0,0,NULL,NULL,NULL) as LOCALIDADE,
	IF (pltb.LocalityID>0, getlocalityFields(pltb.LocalityID, 'LONGITUDE'),IF (pltb.GPSPointID>0, getGPSlocalityFields(pltb.GPSPointID, 'LONGITUDE'), NULL)) AS LONGITUDE,
	IF (pltb.LocalityID>0, getlocalityFields(pltb.LocalityID, 'LATITUDE'),IF (pltb.GPSPointID>0, getGPSlocalityFields(pltb.GPSPointID, 'LATITUDE'), NULL)) AS LATITUDE,
	IF (pltb.LocalityID>0, getlocalityFields(pltb.LocalityID, 'ALTITUDE'),IF (pltb.GPSPointID>0, getGPSlocalityFields(pltb.GPSPointID, 'ALTITUDE'), NULL)) AS ALTITUDE
	FROM Habitat as pltb
	WHERE pltb.HabitatTipo='Local' AND (pltb.LocalityID+pltb.GPSPointID)>0 AND (";
$idx =0;
foreach ($habitats as $vv) {
	if ($idx==0) {
		$qq .= " pltb.HabitatID='".$vv."'";
	} else {
		$qq .= " OR pltb.HabitatID='".$vv."'";
	}
	$idx++;
}
$qq .= ") ORDER BY pltb.PathName";
$res = mysql_query($qq,$conn);

$fh = fopen("temp/".$filename, 'w') or die("nao foi possivel gerar o arquivo");
$hh = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<kml xmlns=\"http://www.opengis.net/kml/2.2\">
<Document>
  <name>".$_SESSION['userlastname']."_".$_SESSION['sessiondate']."</name>
  <description>INSTITUTO NACIONAL DE PESQUISAS DA AMAZÔNIA (INPA), Manaus, Brasil. \nArquivo gerado por ".$url." ".$_SESSION['userfirstname']." ".$_SESSION['userlastname']." em ".$_SESSION['sessiondate']."</description>
  ";
fwrite($fh, $hh);

$tbwidth = 300;
$titlefont = "1em";
$txtfont = "0.8em";

while ($rsw = mysql_fetch_assoc($res)){

$txt .= "
<Placemark>
  <name>".$rsw['HabitatID']."</name>
  <visibility>1</visibility>
  <description>
<![CDATA[
<table width='".$tbwidth."'>
<tr style='font-size: ".$titlefont."'><td>".($rsw['PathName'])."</td></tr>
<tr><td><hr></td></tr>
<tr style='font-size: ".$txtfont."'><td>".$rsw['LOCALIDADE']."</td></tr>
<tr><td><hr></td></tr>";
$habitat = describehabitat($rsw['HabitatID'],$img=FALSE,$conn);
$txt .= "
<tr style='font-size: ".$txtfont."'><td>".$habitat."</td></tr>";

//imagens
$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);
$urlbig = $url."/img/originais/";
$url = $url."/img/lowres/";

$quq = "SELECT HabitatVariation FROM Habitat_Variation as trv JOIN Traits as tr USING(TraitID) WHERE tr.TraitTipo='Variavel|Imagem' AND trv.HabitatID=".$rsw['HabitatID']." ORDER BY tr.TraitName";
$ruq = mysql_query($quq,$conn);
$nruq = mysql_numrows($ruq);
if ($nruq>0) {
$txt .= "
<tr ><td><hr></td></tr>
<tr><td>
<table style='border: 0;' align='center' cellpadding='10'>";
$latarr = array();
$longarr = array();
while ($ruqw = mysql_fetch_assoc($ruq)) {
	$imgs = explode(";",$ruqw['TraitVariation']);
	foreach ($imgs as $vimg) {
		$vimg = $vimg+0;
		$qusq = "SELECT FileName FROM Imagens WHERE ImageID='".$vimg."'";
		//echo $qusq;
		$rusq = mysql_query($qusq,$conn);
		$rusqw = mysql_fetch_assoc($rusq);
		$tutx = "
<tr><td><a href=\"".$urlbig.$rusqw['FileName']."\"><img src=\"".$url.$rusqw['FileName']."\" width=200></a><br></td></tr>";
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
  <Point>
<coordinates>".$rsw['LONGITUDE'].",".$rsw['LATITUDE'].",".$rsw['ALTITUDE']."</coordinates>
  </Point>
</Placemark>";
	fwrite($fh, $txt);
	$latarr[] = $rsw['LATITUDE'];
	$longarr[] = $rsw['LONGITUDE'];	
	}

$txt = "
</Document>
</kml>";
fwrite($fh,$txt);
fclose($fh);

if (count($latarr)>0) {
$latcenter = @array_sum($latarr)/count($latarr);
$longcenter =@array_sum($longarr)/count($longarr);

$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);

$url_filename = $url."/temp/".$filename;
if (file_exists("temp/".$filename)) {
echo "aqui estoy".$latcenter." ".$longcenter." ".$divid;
echo "
<script type=\"text/javascript\">
function initialize() {
var myOptions = {
	center: new google.maps.LatLng(".$latcenter.", ".$longcenter."),
  	zoom: 5,
    mapTypeId: google.maps.MapTypeId.TERRAIN
}

var map = new google.maps.Map(document.getElementById(".$divid."), myOptions);
var ctaLayer = new google.maps.KmlLayer('".$url_filename."',{preserveViewport:true});
ctaLayer.setMap(map);
</script>
}
function addoverlay() {
var myOptions = {
	center: new google.maps.LatLng(".$latcenter.", ".$longcenter."),
  	zoom: 5,
    mapTypeId: google.maps.MapTypeId.TERRAIN
}
var map = new google.maps.Map(document.getElementById(".$divid."), myOptions);
var ctaLayer = new google.maps.KmlLayer('".$url_filename."',{preserveViewport:true});
ctaLayer.setMap(map);
</script>
}

";
} else {
	echo "O arquivo ".$url_filename." não existe<br>";
}

} else {
	echo "O arquivo ".$url_filename." não existe<br>";
}

?>