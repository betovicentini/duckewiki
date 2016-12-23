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
//
$plantaid ="";
$especimenid="";


//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

//CABECALHO
$ispopup=1;
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel=\"stylesheet\" href=\"javascript/jqzoom_ev-2.3/css/jquery.jqzoom.css\" type=\"text/css\">",
"<style type=\"text/css\">
a img,:link img,:visited img { border: none; }
.clearfix:after{clear:both;content:\".\";display:block;font-size:0;height:0;line-height:0;visibility:hidden;}
.clearfix{display:block;zoom:1}
ul#thumblist{display: inline-block;}
ul#thumblist li {float:left; margin-bottom: 4px; list-style:none;}
ul#thumblist li a{display:block;border:1px solid #CCC;}
ul#thumblist li a.zoomThumbActive{ border:1px solid red; }
.jqzoom{
text-decoration:none;
float:left;
}
</style>"
);
$which_java = array(
"<script src=\"javascript/jqzoom_ev-2.3/js/jquery-1.6.js\" type=\"text/javascript\"></script>",
"<script src=\"javascript/jqzoom_ev-2.3/js/jquery.jqzoom-core.js\" type=\"text/javascript\"></script>",
"<script type=\"text/javascript\">
$(document).ready(function() {
$('.jqzoom').jqzoom({
        zoomType: 'standard',
        lens:true,
        preloadImages: false,
        alwaysOn:false,
        zoomWidth: 300,
        zoomHeight: 300,
        xOffset: 0,
        yOffset: 50,
        position:'right',
        preloadText: 'Carregando o zoom',
        title: false
        });
});
</script>",
"<script type=\"text/javascript\">
function changetxtmenu(menuid,txtid){
    var menudest = document.getElementById('imgmenu');
    var txtdest = document.getElementById('oidtxt');
    menudest.innerHTML = document.getElementById(menuid).innerHTML;
    txtdest.innerHTML = document.getElementById(txtid).innerHTML;
}
</script>"
);

$title = 'Mostrar Imagens';
$body = "bgcolor='#4C4646'";
//$body = "";
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";

//echopre($gget);
$detid =0;
$detid = $famid+$genid+$specid+$infspecid+$detid;
$sampleid = $plantaid+$especimenid;
if ($detid>0 || $sampleid>0) {
	$qindet = '';
	$qorder = '';
	//mytable.TraitName
	if ($infspecid>0) {
				$qindet = " WHERE iddet.InfraEspecieID=".$infspecid;
	} 
	else {
		if ($specid>0) {
			$qindet = " WHERE iddet.EspecieID=".$specid;
		} 
		else {
			if ($genid>0) {
				$qindet = " WHERE iddet.GeneroID=".$genid;
				$qorder .= ' ORDER BY mytable.DETERMINACAO, mytable.IDENTIFICADOR ';
			} 
			elseif ($famid>0) {
				$qindet = " WHERE iddet.FamiliaID=".$famid;
				$qorder .= ' ORDER BY mytable.DETERMINACAO, mytable.IDENTIFICADOR ';
			}
		}
	}
	if ($qindet=='' && $sampleid>0) {
		if ($especimenid>0) {
			$qindet2 = " WHERE pltb.EspecimenID=".$especimenid;
		}
		if ($plantaid>0) {
			$qindet = " WHERE pltb.PlantaID=".$plantaid;
		}
	} 
	else {
		$qindet2 = $qindet;
	}

	$qq = "SELECT * FROM (";
	if ($qindet<>'') {
      $qq .= "(SELECT 
      IF(iddet.InfraEspecieID>0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,' </i> ',sptb.EspecieAutor,' <i>',infsptb.InfraEspecieNivel,' ',infsptb.InfraEspecie,'</i> ',infsptb.InfraEspecieAutor), IF(iddet.EspecieID>0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,'</i> ',sptb.EspecieAutor),IF(gentb.GeneroID>0,CONCAT('<i>',gentb.Genero,'</i>'),''))) as DETERMINACAO,
      acentosPorHTML(gettaxonname(pltb.DetID,1,0)) as NOME,
      pltb.DetID,
      famtb.Familia as FAMILIA,
      gentb.Genero as GENERO,
      CONCAT('Planta marcada ',plantatag(pltb.PlantaID)) as IDENTIFICADOR,
      0 as EspecimenID,
      pltb.PlantaID,
      trv.TraitVariation,
      trv.TraitID,
      trids.TraitName
      FROM Plantas as pltb
      LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID 
      LEFT JOIN Traits_variation as trv ON pltb.PlantaID=trv.PlantaID 
      LEFT JOIN Traits as trids ON trids.TraitID=trv.TraitID
      LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID 
      LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID 
      LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  
      LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID
      ".$qindet." AND trids.TraitTipo LIKE '%Image%')";
    }
    if ($qindet<>'' && $qindet2<>'') {   
      $qq .= " UNION ";
    }
    if ($qindet2<>'') {   
      $qq .= " (SELECT 
      IF(iddet.InfraEspecieID>0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,' </i> ',sptb.EspecieAutor,' <i>',infsptb.InfraEspecieNivel,' ',infsptb.InfraEspecie,'</i> ',infsptb.InfraEspecieAutor), IF(iddet.EspecieID>0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,'</i> ',sptb.EspecieAutor),IF(gentb.GeneroID>0,CONCAT('<i>',gentb.Genero,'</i>'),''))) as DETERMINACAO,
      acentosPorHTML(gettaxonname(pltb.DetID,1,0)) as NOME,
      pltb.DetID,      
      famtb.Familia as FAMILIA,
      gentb.Genero as GENERO,
      CONCAT(colpessoa.Abreviacao,' ',IF(pltb.Prefixo IS NULL OR pltb.Prefixo='','',CONCAT(pltb.Prefixo,'-')), pltb.Number,IF(pltb.Sufix IS NULL OR pltb.Sufix='','',CONCAT('-',pltb.Sufix))) as IDENTIFICADOR,
      pltb.EspecimenID,
      pltb.PlantaID,
      trv.TraitVariation,
      trv.TraitID,
      trids.TraitName
      FROM Especimenes as pltb
      LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID 
      LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID
      LEFT JOIN Traits_variation as trv ON pltb.EspecimenID=trv.EspecimenID 
      LEFT JOIN Traits as trids ON trids.TraitID=trv.TraitID
      LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID 
      LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID 
      LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  
      LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID
      ".$qindet2." AND trids.TraitTipo LIKE '%Image%')";
    }
    $qq .= ") AS mytable ".$qorder;
	//echo "<span style='color: red; font-size: 2em;'>".$qq."</span><br >";
	$res = mysql_query($qq,$conn);
	$txt = '';
	$i=0;
	$trn = '';
	$nperline = 4;
	$rwidx = 1;

	$url = $_SERVER['HTTP_REFERER'];
	$uu = explode("/",$url);
	$nu = count($uu)-1;
	unset($uu[$nu]);
	$url = implode("/",$uu);
	$urlbig = $url."/img/originais/";
	$urllow = $url."/img/lowres/";
	$pthumb = $url."/img/thumbnails/";
	$path =   $url."/img/copias_baixa_resolucao/";
	$stilo =" border:1px solid #cccccc;  -webkit-box-shadow:inset 0 0 6px #cccccc; -moz-box-shadow: inset 0 0 6px #cccccc; cursor: pointer;";
	$iconheight = "height=\"20\"";
	$tohide ='';
while ($rsw = mysql_fetch_assoc($res)) {
	$onome = $rsw['DETERMINACAO'];
	$oiden = $rsw['IDENTIFICADOR'];
	$fams = $rsw['FAMILIA'];
	$specimenid = $rsw['EspecimenID'];
	$pltid = $rsw['PlantaID'];
	$currdetid = $rsw['DetID'];
	$simplenome = $rsw['NOME'];
	$otraitid = $rsw['TraitID'];
	if ($specimenid>0) {
		$oidtxt = "<span style='font-size: 0.8em; color: yellow;' >".$simplenome."</span><br/><a style='font-size: 0.8em; color: #99FFFF; cursor: pointer;' onclick=\"javascript:small_window('".$url."/showspecimen.php?especimenid=".$specimenid."',400,500,'Mostrar Notas Espécimen');\" onmouseover=\"Tip('Mostrar Notas da Amostra ".$oiden."');\" ><u>".$oiden."</u></a>
		";
		$specref = "especimenid=".$specimenid;
	} elseif ($pltid>0) {
		$oidtxt = "<span style='font-size: 0.8em; color: yellow;' >".$simplenome."</span><br/><a style='font-size: 0.8em; color: #99FFFF; cursor: pointer;' onclick=\"javascript:small_window('".$url."/showplanta.php?plantaid=".$pltid."',400,500,'Mostrar Notas Plantas');\" onmouseover=\"Tip('Mostrar Notas da Planta ".$oiden."');\"><u>".$oiden."</u></a>";
		$specref = "plantaid=".$pltid;
	}
if ($uuid>0 && $acclevel!='visitor') {
	$detimg = "<img src=\"icons/diversity.png\" ".$iconheight." style=\"".$stilo."\" onclick = \"javascript:small_window('".$url."/taxonomia-popup.php?updatechecklist=1&ispopup=1&saveit=true&".$specref."&getplspid=again&reloadwin=1',1000,500,'Editar Identificação');\" onmouseover=\"Tip('Editar Identificação da amostra ".$oiden."');\" />";
	$menushow= TRUE;
} 
else {
	$detimg = '';
	$menushow=FALSE;
}
	$timgs = explode(";",$rsw['TraitVariation']);
	if ($i==0) {
	$tt2 = '';
	if ($infspecid>0) {
			$tt2 = $onome." [".$fams."]";
	} 
	else {
		if ($specid>0) {
			$tt2 = $onome." [".$fams."]";
		} 
		else {
			if ($genid>0) {
				$tt2 = $rsw['GENERO'];
			} 
			elseif ($famid>0) {
				$tt2 = $fams;
			} else {
				if ($specimenid>0 || $pltid>0) {
					$tt2 = $oiden;
					if (!empty($onome)) { $tt2 .= "   ".$onome;}
					if (!empty($fams)) { $tt2 .= " [".$fams."] ";}
				}
			}
		}
	}
	$txt .= "
<table style=\"border: 0;\" align='left'  cellpadding='7' >
  <tr><td valign='middle' style=\"color: #D4A017; font-size: 1.2em; font-style: bold;\" >
Imagens para ".$tt2."</td></tr>
  <tr><td valign='middle' </td></tr>
</table>";
	}
//<div id=\"imagelist\" style=\"height:600px;width:200px; float:left;\">";
//	if ($trn!=$rsw['TraitName']) {
//	if ($trn!='') {
//	$txt .= "
//  </table>";
//}
//$txt .= "
//<br />
//  <hr>
//  <table width='100%'>
//    <tr><td valign='middle' style=\"color: white; font-size: 1.5em; font-style: bold;\" colspan='100%'>".$rsw['TraitName']."</td></tr>
//  </table>
//  <hr>
//  <table width='60%'>";
//	}
	$timgs = array_unique($timgs);
	foreach ($timgs as $vimg) {
		$vimg = $vimg+0;
		$qusq = "SELECT FileName,addcolldescr(Autores) as Fotografos,DateOriginal FROM Imagens WHERE ImageID='".$vimg."'";
		$rusq = mysql_query($qusq,$conn);
		$rusqw = mysql_fetch_assoc($rusq);

		
		//nome de imagem para quando estiver faltando (adicionado para desenvolvimento apenas)
		$pathcpbres = $path.$rusqw['FileName'];
		$imgn = rand(1,4);
		$imgn = "semimagem".$imgn.".jpg";
		if (!file_exists($pathcpbres)) {
		    $opath = $path.$imgn;
		    $ourllow = $urllow.$imgn;
		} else {
		    $opath = $path.$rusqw['FileName'];
		    $ourllow = $urllow.$rusqw['FileName'];
		}


		//echo $ourllow."<br >";
		//if ($rwidx==1 || $rwidx==5){
			//if ($rwidx>1) { 
			//	$txt .= "
        //</tr>";
		//	}
		//	$txt .= "
        //<tr>";
        	//if ($rwidx==5) {
        		//$rwidx=1;
        	//}
		//}
		if ($menushow) {
		$imgnota = "<img src=\"icons/nota-icon.png\" ".$iconheight." style=\"".$stilo."\" onclick = \"javascript:small_window('".$url."/image_measure.php?".$specref."&imgid=".$vimg."',1000,600,'Extraindo dados de imagens');\" onmouseover=\"Tip('Para coletar dados das imagens');\" />";
		$imgerros = "<img src=\"icons/imagechange.png\" ".$iconheight." style=\"".$stilo."\" onclick = \"javascript:small_window('".$url."/image_change.php?".$specref."&imgid=".$vimg."&otraitid=".$otraitid."',1000,500,'A imagem não é dessa espécie');\" onmouseover=\"Tip('Clicar se a imagem não for dessa espécie ou da amostra ".$oiden."');\" />";
		$menu = "&nbsp;".$detimg."&nbsp;".$imgnota."&nbsp;".$imgerros;
		} else {
		$menu='';
		}
		$imgsee = "<img src=\"icons/search_plus_blue.png\" ".$iconheight." style=\"".$stilo."\" onclick = \"javascript:small_window('".$url."/image_seelarge.php?".$specref."&imgid=".$vimg."',1000,600,'Vendo imagem original');\" onmouseover=\"Tip('Para ver a imagem original');\" />";
		$menu = $imgsee.$menu;
//title=\"".$simplenome."\"
		if ($i==0) {
$txt .= "
<div class=\"clearfix\" id=\"content\"  style=\"width: 98%; border: thin white;\" >
    <div class=\"clearfix\" style=\"margin-left: 180px; margin-top: 50px; height:500px; width: 800px; position: absolute;\" >
        <span id='imgmenu' >".$menu."</span>&nbsp;&nbsp;<span style=\"color: white; font-size: 0.6em;\" >Passe o mouse na imagem para zoom!</span>
<br />
        <a href=\"".$opath."\" class=\"jqzoom\" rel='gal1'  >
            <img src=\"".$ourllow."\"    title=\"\" style=\"border: 4px solid #666;\">
        </a>
<span id='oidtxt'>".$oidtxt."</span>
    </div>
 <div class=\"clearfix\" style=\"position: absolute; margin-top: 50px; height:500px; width:170px; overflow-y: auto;  border: 1px solid #ffffff;\">
<ul id=\"thumblist\" class=\"clearfix\" >
<li><a class=\"zoomThumbActive\" href='javascript:void(0);' rel=\"{gallery: 'gal1', smallimage: '".$ourllow."',largeimage: '".$opath."', title: '".$simplenome."' }\"><img width=100px; src='".$ourllow."' onmouseover=\"Tip('".$simplenome." - ".$oiden."');\"  onclick=\"javascript:changetxtmenu('".$vimg."menu','".$vimg."txt');\" ></a></li>";
		} else {
		$txt .= "
<li><a href='javascript:void(0);' rel=\"{gallery: 'gal1', smallimage: '".$ourllow."',largeimage: '".$opath."', title: '".$simplenome."'}\"><img width=100px; src='".$ourllow."' onmouseover=\"Tip('".$simplenome." - ".$oiden."');\"  onclick=\"javascript:changetxtmenu('".$vimg."menu','".$vimg."txt');\" title: '".$simplenome."' ></a></li>";
	}
	$tohide .= "<span id='".$vimg."menu' style='visibility:hidden;' >".$menu."</span>
<span id='".$vimg."txt' style='visibility:hidden;' >".$oidtxt."</span>";

	//$stilo2 =" border:1px solid #ffffff;  -webkit-box-shadow:inset 0 0 6px #cccccc; -moz-box-shadow: inset 0 0 6px #cccccc;";
//	$txt .= "
//<br />
//<div style=\"width: 150px; align: center; ".$stilo2."\">
//<a href=\"".$path.$rusqw['FileName']."\" class='MagicZoomPlus' rel=\"zoom-position:center;zoom-width:500px; zoom-fade:true;smoothing-speed:17;opacity-reverse:true;\" ><img width=150px; src=\"".$urllow.$rusqw['FileName']."\"/></a>
//<br/>".$oidtxt."</div>
//";
//////$txt .= "
//////          <td>
////            <table>
////              <tr>
////                <td style=\"border: 5px solid white; border-collapse:collapse;\">
////                  <a href=\"".$path.$rusqw['FileName']."\" class='MagicZoomPlus' rel=\"zoom-position:center;zoom-width:400px; zoom-fade:true;smoothing-speed:17;opacity-reverse:true;\" ><img width='150' src=\"".$urllow.$rusqw['FileName']."\"/></a>
////                </td>
////              </tr>
////            <tr>
////              <td ><table><tr><td valign='middle' >".$oidtxt."</td><td valign='bottom' align='center'>".$detimg."</td></tr></table>
////                <!--- <br />
////                ".$rusqw['Fotografos']." [".$rusqw['DateOriginal']."] --->
////              </td>
////            </tr>
////          </table>
////        </td>";
        $rwidx++;
        $i++;
	}     
	$trn = $rsw['TraitName'];
}
//if ($rwidx<($nperline+1)){
//	for ($k = $rwidx; $k <= $nperline; $k++) {
//$txt .= "<td>&nbsp</td>";
//	}
//}
//$txt .= "
//      </tr>";
//$txt .= "
//     </table>
$txt .= "
</ul>
</div>
</div>
     ";
//</div>
echo $txt;
echo "<br>".$tohide;

}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=FALSE);
?>