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
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Mostrar Planta';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$formnotes = 0;
if ($plantaid>0) {
$qq .= " SELECT 
pltb.PlantaID AS WikiPlantaID,
plantatag(pltb.PlantaID) as TAG_PlantaMarcada, 
IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as NOME,
famtb.Familia as FAMILY,
IF(iddet.InfraEspecieID>0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,' </i> ',sptb.EspecieAutor,' <i>',infsptb.InfraEspecieNivel,' ',infsptb.InfraEspecie,'</i> ',infsptb.InfraEspecieAutor), IF(iddet.EspecieID>0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,'</i> ',sptb.EspecieAutor),IF(gentb.GeneroID>0,CONCAT('<i>',gentb.Genero,'</i>'),''))) as DETERMINACAO,
CONCAT(detpessoa.Abreviacao,' [',DATE_FORMAT(iddet.DetDate,'%d-%b-%Y'),']') as detdetby,
localidadestring(pltb.GazetteerID+0,pltb.GPSPointID+0,0,0,0,IF(ABS(pltb.Longitude+0)>0,pltb.Latitude+0,IF(pltb.GPSPointID>0,gpspt.Latitude+0,IF(ABS(gaz.Longitude+0)>0,gaz.Latitude+0,muni.Latitude+0))),IF(ABS(pltb.Longitude)>0,pltb.Longitude+0,IF(pltb.GPSPointID>0,gpspt.Longitude+0,IF(ABS(gaz.Longitude)>0,gaz.Longitude+0,NULL))),IF(ABS(pltb.Longitude)>0,pltb.Altitude+0,IF(pltb.GPSPointID>0,gpspt.Altitude+0,IF(ABS(gaz.Longitude)>0,gaz.Altitude+0,0)))) as LOCALIDADE,
IF(ABS(pltb.Longitude)>0,pltb.Longitude,IF(pltb.GPSPointID>0,gpspt.Longitude,IF(ABS(gaz.Longitude)>0,gaz.Longitude,muni.Longitude))) as LONGITUDE,
IF(ABS(pltb.Longitude)>0,pltb.Latitude,IF(pltb.GPSPointID>0,gpspt.Latitude,IF(ABS(gaz.Longitude)>0,gaz.Latitude,muni.Latitude))) as LATITUDE,
IF(ABS(pltb.Longitude)>0,pltb.Altitude,IF(pltb.GPSPointID>0,gpspt.Altitude,IF(ABS(gaz.Longitude)>0,gaz.Altitude,''))) as ALTITUDE,
IF(ABS(pltb.Longitude)>0,'GPS-planta',IF(pltb.GPSPointID>0,'GPS',IF(ABS(gaz.Longitude)>0,'Localidade','Municipio'))) as COORD_PRECISION,
IF(pltb.HabitatID>0,pltb.HabitatID,0) as HabitatID,
vernaculars(pltb.VernacularIDS) as NOME_VULGAR,
projetostring(pltb.ProjetoID,1,0) as PROJETO,
projetologo(pltb.ProjetoID) as PROJETOlogofile,
labeldescricao(0,pltb.PlantaID+0,".$formnotes.",FALSE,FALSE) as NOTAS";
$qq .= " FROM Plantas as pltb 
LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID 
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
LEFT JOIN Province as provigps ON munigps.ProvinceID=provigps.ProvinceID  
LEFT JOIN Country  as countrygps ON provigps.CountryID=countrygps.CountryID
LEFT JOIN Projetos ON pltb.ProjetoID=Projetos.ProjetoID";
	$qq .= " WHERE pltb.PlantaID='".$plantaid."'";
	$qqq = $qq;
	$res = mysql_query($qqq,$conn);
	$txt = '';
	$rsw = mysql_fetch_assoc($res);

	//echo $qqq."<br />";
	$quq = "SELECT TraitVariation FROM Traits_variation as trv JOIN Traits as tr USING(TraitID) WHERE tr.TraitTipo='Variavel|Imagem' AND trv.PlantaID=".$rsw['WikiPlantaID']." ORDER BY tr.TraitName";
	$ruq = mysql_query($quq,$conn);
	$nruq = mysql_numrows($ruq);

$titfont = "2";
$txtfont = "1";
if (!empty($rsw['TAG_PlantaMarcada'])) {
$txt = "
<hr>
<font size='".$titfont."'><b>PLANTA MARCADA #".$rsw['TAG_PlantaMarcada']."</b></font>
";
}
$txt .= 
"<hr>
<font size='".$titfont."'>".strtoupper($rsw['FAMILY'])."</font>
<br /><font size='".$titfont."'>".$rsw['DETERMINACAO']." ".$tkt."</font>
";
if (!empty($rsw['detdetby'])) {
$txt .= "<br />
<font size='".$txtfont."'>Identificado por ".$rsw['detdetby']."</font>";
}
$dethist = returnDEThistoryAStable($rsw['WikiPlantaID'],0,$conn);
if (count($dethist)>0) {
$txt .= "
<hr>
<font size='".$titfont."'><b>Histórico das identificações</b>";
foreach ($dethist as $detvv) {
	$txt .= "<br />".$detvv;
}
$txt .= "</font>";
}
if (!empty($rsw['NOME_VULGAR'])) {
$txt .= "<br />
<font size='".$txtfont."'>Nome vulgar: ".$rsw['NOME_VULGAR']."</font><br />";
}

$txt .= "
<hr>
<font size='".$titfont."'>".$rsw['LOCALIDADE']."</font>";
if ($rsw['HabitatID']>0) {
$habitat = describehabitat($rsw['HabitatID'],$img=FALSE,$conn);
$txt .= "
<hr>
<font size='".$txtfont."'>".$habitat."</font>";
}
if (!empty($rsw['NOTAS'])) {
	$txt .= "
<hr>
<font size='".$txtfont."'><b>NOTAS</b>: ".$rsw['NOTAS']."</font>";
}


//imagens
$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);

//PROJETO
$urllogo = $url."/";
if ($rsw['PROJETOlogofile']!='') {
	$img = $urllogo.$rsw['PROJETOlogofile'];
} else {
	$img = $urllogo."icons/inpa_gov.png";
}
$txt .= "
<hr>
<table style='border: 0;' align='center' cellpadding='3'>
<tr><td>
<img src=\"".$img."\" height='60'></td>";
if (!empty($rsw['PROJETO'])) {
	$txt .= "
<td><font size=2>".$rsw['PROJETO']."</font></td>";
}
$txt .= "
</tr>
</table>
<hr>";

//IMAGENS
$urlbig = $url."/img/originais/";
$url = $url."/img/lowres/";
if ($nruq>0) {
$txt .= "
<table style='border: 0;' align='center' cellpadding='10'>";

while ($ruqw = mysql_fetch_assoc($ruq)) {
	$imgs = explode(";",$ruqw['TraitVariation']);
	foreach ($imgs as $vimg) {
		$vimg = $vimg+0;
		$qusq = "SELECT FileName FROM Imagens WHERE ImageID='".$vimg."'";
		//echo $qusq;
		$rusq = mysql_query($qusq,$conn);
		$rusqw = mysql_fetch_assoc($rusq);
		
		//coloca imagem falsa se não encontrou na pasta (para exemplo em desenvolvimento)
		$pathcpbres = $urlbig.$rusqw['FileName'];
		$imgn = rand(1,4);
		$imgn = "semimagem".$imgn.".jpg";
		if (!file_exists($pathcpbres)) {
		    $opath = $urlbig.$imgn;
		    $ourllow = $url.$imgn;
		} else {
		    $opath = $urlbig.$rusqw['FileName'];
		    $ourllow = $url.$rusqw['FileName'];
		}
		//////
		
		$tutx = "
<tr><td><a href=\"".$opath."\"><img src=\"".$ourllow."\" width=300></a><br /></td></tr>";
		$txt .= $tutx;
	}
}
$txt .= "
</table>
<hr>";
}
echo $txt;
} 
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>