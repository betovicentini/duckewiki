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
if (!empty($_POST['detset'])) {
	$detset = $_POST['detset'];
	unset($_POST['detset']);
}
if (!empty($_GET['detset'])) {
	$detset = $_GET['detset'];
	unset($_GET['detset']);
}

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
);
$which_java = array();
$title =  'Informação de uma planta';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$qq = " SELECT pltb.PlantaID AS WikiPlantaID, 
	pltb.PlantaTag as PLANTATAG, 
	IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as NOME,
	famtb.Familia as FAMILY,
	IF(iddet.InfraEspecieID>0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,' </i> ',sptb.EspecieAutor,' <i>',infsptb.InfraEspecieNivel,' ',infsptb.InfraEspecie,'</i> ',infsptb.InfraEspecieAutor), IF(iddet.EspecieID>0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,'</i> ',sptb.EspecieAutor),IF(gentb.GeneroID>0,CONCAT('<i>',gentb.Genero,'</i>'),''))) as DETERMINACAO,
	CONCAT(detpessoa.Abreviacao,' [',DATE_FORMAT(iddet.DetDate,'%d-%b-%Y'),']') as detdetby,
localidadestring(pltb.GazetteerID,pltb.GPSPointID,0,0,0,0,0,0) as LOCALIDADE,
	IF(pltb.HabitatID>0,pltb.HabitatID,0) as HabitatID,
	projetostring(pltb.ProjetoID,1,0) as PROJETO,
	projetologo(pltb.ProjetoID) as PROJETOlogofile,
	labeldescricao(0,pltb.PlantaID+0,0,FALSE,FALSE) as NOTAS";
	$qq .= " FROM Plantas as pltb 
	LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID 
	LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID 
	LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID 
	LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  
	LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID 
	LEFT JOIN Pessoas as detpessoa ON detpessoa.PessoaID=iddet.DetbyID
	LEFT JOIN Projetos ON pltb.ProjetoID=Projetos.ProjetoID WHERE pltb.PlantaID='".$plantaid."'";
$res = mysql_query($qq,$conn);
//echo $qq;
$rsw = mysql_fetch_assoc($res);
$quq = "SELECT TraitVariation FROM Traits_variation as trv JOIN Traits as tr USING(TraitID) WHERE tr.TraitTipo='Variavel|Imagem' AND trv.PlantaID=".$rsw['WikiPlantaID']." ORDER BY tr.TraitName";
$ruq = mysql_query($quq,$conn);
$nruq = mysql_numrows($ruq);

$txt = 
"<div style='margin: 10px; width: 50%' >
<font size=4><b>Árvore No. ".$rsw['PLANTATAG']."</b></font>
<hr>
<font size=3>".strtoupper($rsw['FAMILY'])."</font>
<br><font size=3>".$rsw['DETERMINACAO']." ".$tkt."</font>
";
if (!empty($rsw['detdetby'])) {
$txt .= "<br>
<font size=2>Identificado por ".$rsw['detdetby']."</font>";
}
$dethist = returnDEThistoryAStable($rsw['WikiPlantaID'],0,$conn);
if (count($dethist)>0) {
$txt .= "
<hr>
<font size=2><b>Histórico das identificações</b>";
foreach ($dethist as $detvv) {
	$txt .= "<br>".$detvv;
}
$txt .= "</font>";
}
$txt .= "<hr>
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
<font size=2><b>Notas</b>: ".$rsw['NOTAS']."</font>";
}
//imagens
$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);
$url1 = implode("/",$uu);
$urlbig = $url."/img/originais/";
$url = $url."/img/lowres/";

$txt .= "
<hr>
<table style='border: 0;' align='center' cellpadding='3'>
<tr><td>
<img src=\"".$url1."/icons/inpa_gov.png\" width=150></td>";
if (!empty($rsw['PROJETO'])) {
	$txt .= "
<td><font size=2>".$rsw['PROJETO']."</font></td>";
}
$txt .= "
</tr></table><br>";




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
<tr><td><a href=\"".$urlbig.$rusqw['FileName']."\"><img src=\"".$url.$rusqw['FileName']."\" width=300></a><br></td></tr>";
		$txt .= $tutx;
	}
}
$txt .= "
</table>
<hr>";
}
echo $txt."</div>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>