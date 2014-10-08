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
$title = 'Mostrar Especímene';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$formnotes = 0;

if ($especimenid>0) {
$qq .= " SELECT 
	pltb.EspecimenID AS WikiEspecimenID, 
	CONCAT(colpessoa.SobreNome,'_',IF(pltb.Prefixo IS NULL OR pltb.Prefixo='','',CONCAT(pltb.Prefixo,'-')), pltb.Number,IF(pltb.Sufix IS NULL OR pltb.Sufix='','',CONCAT('-',pltb.Sufix))) as IDENTIFICADOR, 
	CONCAT(colpessoa.PreNome,' ',colpessoa.SegundoNome,' ',colpessoa.SobreNome) as COLLECTOR, 
	pltb.Number as NUMBER, 
	CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day) as DATA_COLETA, 
	addcolldescr(AddColIDS) as ADDCOLL,
	IF (pltb.INPA_ID>0,pltb.INPA_ID,0) as INPA_NUM,
	IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as NOME,
	famtb.Familia as FAMILY,
	IF(iddet.InfraEspecieID>0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,' </i> ',sptb.EspecieAutor,' <i>',infsptb.InfraEspecieNivel,' ',infsptb.InfraEspecie,'</i> ',infsptb.InfraEspecieAutor), IF(iddet.EspecieID>0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,'</i> ',sptb.EspecieAutor),IF(gentb.GeneroID>0,CONCAT('<i>',gentb.Genero,'</i>'),''))) as DETERMINACAO,
	CONCAT(detpessoa.Abreviacao,' [',DATE_FORMAT(iddet.DetDate,'%d-%b-%Y'),']') as detdetby,
	localidadestring(pltb.GazetteerID,pltb.GPSPointID,pltb.MunicipioID,pltb.ProvinceID,pltb.CountryID,IF(ABS(pltb.Longitude)>0,pltb.Latitude,IF(pltb.GPSPointID>0,gpspt.Latitude,IF(ABS(gaz.Longitude)>0,gaz.Latitude,muni.Latitude))),IF(ABS(pltb.Longitude)>0,pltb.Longitude+0,IF(pltb.GPSPointID>0,gpspt.Longitude+0,IF(ABS(gaz.Longitude)>0,gaz.Longitude+0,NULL))),IF(ABS(pltb.Longitude)>0,pltb.Altitude,IF(pltb.GPSPointID>0,gpspt.Altitude,IF(ABS(gaz.Longitude)>0,gaz.Altitude,'')))) as LOCALIDADE,
	IF(pltb.GPSPointID>0,countrygps.Country,IF(pltb.GazetteerID>0,countrygaz.Country,' ')) as COUNTRY,
	IF(pltb.GPSPointID>0,provigps.Province,IF(pltb.GazetteerID>0,provgaz.Province,' ')) as MAJORAREA,
	IF(pltb.GPSPointID>0,munigps.Municipio,IF(pltb.GazetteerID>0,muni.Municipio,' ')) as MINORAREA,
	IF(pltb.GPSPointID>0,gazgps.PathName,IF(pltb.GazetteerID>0,gaz.PathName,' ')) as GAZETTEER,
	IF(pltb.GPSPointID>0,pltb.GPSPointID,'') as GPSpointID,
	IF(pltb.GPSPointID>0,gazgps.Gazetteer,IF(pltb.GazetteerID>0,gaz.Gazetteer,'')) as GAZETTEER_SPECIFIC,
	IF(ABS(pltb.Longitude)>0,pltb.Longitude,IF(pltb.GPSPointID>0,gpspt.Longitude,IF(ABS(gaz.Longitude)>0,gaz.Longitude,muni.Longitude))) as LONGITUDE,
	IF(ABS(pltb.Longitude)>0,pltb.Latitude,IF(pltb.GPSPointID>0,gpspt.Latitude,IF(ABS(gaz.Longitude)>0,gaz.Latitude,muni.Latitude))) as LATITUDE,
	IF(ABS(pltb.Longitude)>0,pltb.Altitude,IF(pltb.GPSPointID>0,gpspt.Altitude,IF(ABS(gaz.Longitude)>0,gaz.Altitude,''))) as ALTITUDE,
	IF(ABS(pltb.Longitude)>0,'GPS-planta',IF(pltb.GPSPointID>0,'GPS',IF(ABS(gaz.Longitude)>0,'Localidade','Municipio'))) as COORD_PRECISION,
	IF(pltb.PlantaID>0,plspectb.PlantaTag,'') as TAG_PlantaMarcada,
	IF(pltb.HabitatID>0,pltb.HabitatID,IF(plspectb.HabitatID>0,plspectb.HabitatID,0)) as HabitatID,
	vernaculars(pltb.VernacularIDS) as NOME_VULGAR,
	projetostring(pltb.ProjetoID,1,0) as PROJETO,
	projetologo(pltb.ProjetoID) as PROJETOlogofile,
	labeldescricao(pltb.EspecimenID+0,pltb.PlantaID+0,".$formnotes.",FALSE,FALSE) as NOTAS";
	//	IF(pltb.GPSPointID>0,CONCAT(gazgps.GazetteerTIPOtxt,' ',gazgps.Gazetteer),IF(pltb.GazetteerID>0,CONCAT(gaz.GazetteerTIPOtxt,' ',gaz.Gazetteer),'')) as GAZETTEER_SPECIFIC,
	$qq .= " FROM Especimenes as pltb 
	LEFT JOIN Plantas as plspectb ON pltb.PlantaID=plspectb.PlantaID 
	LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID
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
	$qq .= " WHERE pltb.EspecimenID='".$especimenid."'";
	$qqq = $qq;
	$res = mysql_query($qqq,$conn);
	$txt = '';
	$rsw = mysql_fetch_assoc($res);

	$quq = "SELECT TraitVariation FROM Traits_variation as trv JOIN Traits as tr USING(TraitID) WHERE tr.TraitTipo='Variavel|Imagem' AND trv.EspecimenID=".$rsw['WikiEspecimenID']." ORDER BY tr.TraitName";
	$ruq = mysql_query($quq,$conn);
	$nruq = mysql_numrows($ruq);

$txt = 
"<div style='margin: 10px; width: 50%' >
<font size='3'>".strtoupper($rsw['FAMILY'])."</font>
<br /><font size='3'>".$rsw['DETERMINACAO']." ".$tkt."</font>
";
if (!empty($rsw['detdetby'])) {
$txt .= "<br />
<font size=2>Identificado por ".$rsw['detdetby']."</font>";
}
$dethist = returnDEThistoryAStable(0,$rsw['WikiEspecimenID'],$conn);
if (count($dethist)>0) {
$txt .= "
<hr>
<font size=2><b>Histórico das identificações</b>";
foreach ($dethist as $detvv) {
	$txt .= "<br />".$detvv;
}
$txt .= "</font>";
}
if (!empty($rsw['NOME_VULGAR'])) {
$txt .= "<br />
<font size='3'>Nome vulgar: ".$rsw['NOME_VULGAR']."</font><br />";
}
$txt .= "<hr>
<font size='3'><b>".$rsw['COLLECTOR']." No. ".$rsw['NUMBER']."</b>
<br />&amp; ".$rsw['ADDCOLL']." em ".$rsw['DATA_COLETA']."</font><br />";
if (($rsw['INPA_NUM']+0)>0) {
$txt .= "
<font size='3'>Depositado em INPA #".$rsw['INPA_NUM']."</font><br />";
}
if (!empty($rsw['TAG_PlantaMarcada'])) {
$txt .= "
<font size=2><b>Planta marcada</b>: #".$rsw['TAG_PlantaMarcada']."</font><br />";
}
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
<table style='border: 0;' align='center' cellpadding='3'  >
<tr><td>
<img src=\"".$img."\" width='100px'></td>";
if (!empty($rsw['PROJETO'])) {
	$txt .= "
<td><font size=2>".$rsw['PROJETO']."</font></td>";
}
$txt .= "
</tr></table><br />";

//IMAGENS
$urlbig = $url."/img/originais/";
$url = $url."/img/lowres/";
if ($nruq>0) {
$txt .= "
<hr>
<table style='border: 0;' align='left' cellpadding='10' >";

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
echo $txt."</div>";
} 
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>