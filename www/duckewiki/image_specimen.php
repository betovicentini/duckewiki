<?php
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
if (!isset($_SESSION['dbname']) && isset($_GET['sessionvars'])) {
	$sss = explode("-",$_GET['sessionvars']);
	foreach($sss as $vv) {
		$vvv = explode("^",$vv);
		$_SESSION[$vvv[0]] = $vvv[1];
	}
	unset($_GET['sessionvars']);
}
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);
$body = "bgcolor='#4C4646'";
ImgHeader($title,$body);

if ($specimenid>0 || $plantaid>0) {
	$qq = " SELECT 
	IF(iddet.InfraEspecieID>0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,' </i> ',sptb.EspecieAutor,' <i>',infsptb.InfraEspecieNivel,' ',infsptb.InfraEspecie,'</i> ',infsptb.InfraEspecieAutor), IF(iddet.EspecieID>0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,'</i> ',sptb.EspecieAutor),IF(gentb.GeneroID>0,CONCAT('<i>',gentb.Genero,'</i>'),''))) as DETERMINACAO,
	pltb.PlantaID";
	
	if ($specimenid>0) {
	$qq .= ", 
	CONCAT(colpessoa.SobreNome,'_',IF(pltb.Prefixo IS NULL OR pltb.Prefixo='','',CONCAT(pltb.Prefixo,'-')), pltb.Number,IF(pltb.Sufix IS NULL OR pltb.Sufix='','',CONCAT('-',pltb.Sufix))) as IDENTIFICADOR 
	FROM Especimenes as pltb
	LEFT JOIN Plantas as plspectb ON pltb.PlantaID=plspectb.PlantaID";
	} else {
	$qq .= ", 
	pltb.PlantaTag as IDENTIFICADOR
	FROM Plantas as pltb";	
	}
	$qq .= "
	LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID
	LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID 
	LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID 
	LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID 
	LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  
	LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID 
	LEFT JOIN Pessoas as detpessoa ON detpessoa.PessoaID=iddet.DetbyID";
	
	if ($specimenid>0) {
	$qq .= " WHERE pltb.EspecimenID='".$specimenid."'";
	} else {
	$qq .= " WHERE pltb.PlantaID='".$plantaid."'";
	}
	
	//echo $qq."<br>";
	$qqq = $qq;
	$res = mysql_query($qqq,$conn);
	$txt = '';
	$rsw = mysql_fetch_assoc($res);
	
	$onome = $rsw['DETERMINACAO'];
	$oiden = $rsw['IDENTIFICADOR'];
	$pltid = $rsw['PlantaID']+0;


	$quq = "SELECT TraitVariation FROM Traits_variation as trv JOIN Traits as tr USING(TraitID) WHERE tr.TraitTipo LIKE '%Imag%' ";
	if ($specimenid>0 && $pltid>0) {
	$quq .= " AND (trv.EspecimenID=".$specimenid." OR trv.PlantaID=".$pltid.")  ORDER BY tr.TraitName";
	} else {
		if ($specimenid>0 && $pltid==0) {
			$quq .= " AND (trv.EspecimenID=".$specimenid.")  ORDER BY tr.TraitName";
		} else {
			if ($plantaid>0) {
				$quq .= " AND (trv.PlantaID=".$plantaid.")  ORDER BY tr.TraitName";
			}
		}
	}
	$ruq = mysql_query($quq,$conn);
	$url = $_SERVER['HTTP_REFERER'];
	$uu = explode("/",$url);
	$nu = count($uu)-1;
	unset($uu[$nu]);
	$url = implode("/",$uu);
	$urlbig = $url."/img/originais/";
	$urllow = $url."/img/lowres/";
	$pthumb = $url."/img/thumbnails/";
	$path =   $url."/img/copias_baixa_resolucao/";


	$txt .= "
	<table style=\"border: 0;\" align='left' >
	<tr><td valign='middle' class='tdsmallbold' style=\"color: #D4A017; font-size: 1.1em; font-style: bold;\" colspan='100%'>
Imagens para ".($oiden)."<br>$onome</td></tr>
	<tr><td valign='middle' style=\"color: white; font-size: 0.6em;\" colspan='100%'>
Zoom on mouse over!</td></tr>";
	while ($ruqw = mysql_fetch_assoc($ruq)) {
		$imgs = explode(";",$ruqw['TraitVariation']);
		//$txt .= "<tr>";
		foreach ($imgs as $vimg) {
			$vimg = $vimg+0;
			$qusq = "SELECT FileName,addcolldescr(Autores) as Fotografos,DateOriginal FROM Imagens WHERE ImageID='".$vimg."'";
			$rusq = mysql_query($qusq,$conn);
			$rusqw = mysql_fetch_assoc($rusq);
			$tutx = "<tr><td align=\"left\"><table><tr><td style=\"border: 5px solid white; border-collapse:collapse;\"><a href=\"".$path.$rusqw['FileName']."\" class='MagicZoomPlus' rel=\"zoom-position:center;zoom-width:400px; zoom-fade:true;smoothing-speed:17;opacity-reverse:true;\" ><img width='150' src=\"".$urllow.$rusqw['FileName']."\"/></a></td><td valign='middle' class='tdformnotes' style=\"color: white;\">".$rusqw['Fotografos']." [".$rusqw['DateOriginal']."]</td>
			</tr></table></td>
			</tr>";
			$txt .= $tutx;
		}
		//$txt .= "</tr>";
	}
	$txt .= "
	
<tr>
<td>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
</td>
<td></td>
</tr>
</table>
";
	echo $txt;
}

PopupTrailers();

?>