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
if(!isset($uuid) || 
	(trim($uuid)=='')) {
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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />",
"<link rel='stylesheet' href='javascript/magiczoomplus/magiczoomplus/magiczoomplus.css' type='text/css' media='screen' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>",
"<script src='javascript/magiczoomplus/magiczoomplus/magiczoomplus.js' type='text/javascript'></script>",
"<script type='text/javascript'>
function chang_det(pltid,sptid,dettxt,useidx) {
  window.document.getElementById('pl_'+useidx).value = pltid;
  window.document.getElementById('sp_'+useidx).value = sptid;
  window.document.getElementById('dettexto_'+useidx).innerHTML = dettxt;
}
</script>"


);
$exdruxulo = 0;

//primeira submissao de dados
if ($submitted==1) {
	if (!empty($nomesciid)) {
		$nn = explode("_",$nomesciid);
		$tipo = $nn[0];
		$id = $nn[1];
		if ($tipo=='infspid') { $qu =  "InfraEspecieID='".$id."'";}
		if ($tipo=='speciesid') { $qu = "EspecieID='".$id."'";}
		if ($tipo=='genusid') { $qu = "GeneroID='".$id."'";}
		if ($tipo=='famid') { $qu = "FamiliaID='".$id."'";}

		$qq = "(SELECT Especimenes.EspecimenID,NULL as PlantaID,TraitVariation,Traits.TraitName,Especimenes.DetID,CONCAT(Abreviacao,' ',Number) as ImgRef,NULL as ImgRef2 FROM Especimenes JOIN Identidade USING(DetID) JOIN Traits_variation ON Especimenes.EspecimenID=Traits_variation.EspecimenID JOIN Traits ON Traits.TraitID=Traits_variation.TraitID JOIN Pessoas ON Especimenes.ColetorID=Pessoas.PessoaID WHERE TraitTipo='Variavel|Imagem' AND Identidade.".$qu.")";

		$qq = $qq." UNION (SELECT NULL as EspecimenID,Plantas.PlantaID,TraitVariation,Traits.TraitName,Plantas.DetID,PlantaTag as ImgRef1,InSituExSitu as ImgRef2 FROM Plantas JOIN Identidade USING(DetID) JOIN Traits_variation ON Plantas.PlantaID=Traits_variation.PlantaID JOIN Traits ON Traits.TraitID=Traits_variation.TraitID WHERE TraitTipo='Variavel|Imagem' AND Identidade.".$qu.")";

		$res = mysql_query($qq,$conn);
		$nres = mysql_numrows($res);
		if ($nres>0) {
			$imagens1=1;
			$uid = $_SESSION['userid'];
			$qq = "DROP TABLE temp_image_".$uid;
			@mysql_query($qq,$conn);
			$tbname = "temp_image_".$uid;
			$qq = "CREATE TABLE ".$tbname." (
				TempID INT(10) unsigned NOT NULL auto_increment,
				PlantaID INT(10),
				EspecimenID INT(10),
				DetID INT(10),
				TraitName VARCHAR(100),
				TaxaNome VARCHAR(100),
				ImageID INT(10),
				ImgRef CHAR(100),
				AddedBy INT(10), 
				AddedDate DATE,
				PRIMARY KEY (TempID))";
			mysql_query($qq,$conn);
			while ($row = mysql_fetch_assoc($res)) {
					$especimenid = $row['EspecimenID'];
					$plantaid = $row['PlantaID'];
					$imgids = explode(";",$row['TraitVariation']);
					$tid = $row['TraitName'];
					$detid = $row['DetID']+0;
					$ref = $row['ImgRef'];
					$ref2 = $row['ImgRef2'];
					if ($plantaid>0) {
						//taxonomy
						$tgn = sprintf("%05s",$ref+0);
						$pp='Tag: ';
						if ($ref2=='Insitu') { $pp = $pp."JB-N-";}
						if ($ref2=='Exsitu') { $pp = $pp."JB-X-";}
						$ref = $pp.$tgn;
					}
					if ($detid>0) {
						$nomenoautor = getdetnoautor($detid,$conn);
					}

					if (count($imgids)>0) {
						foreach ($imgids as $img) {
							$img = $img+0;
							if ($img>0) {
								$arrayofvalues = array(
								'PlantaID' => $plantaid,
								'EspecimenID' => $especimenid,
								'DetID' => $detid,
								'TraitName' => $tid,
								'TaxaNome' => $nomenoautor,
								'ImgRef' => $ref,
								'ImageID' => $img+0);
							$newdetid = InsertIntoTable($arrayofvalues,'TempID',$tbname,$conn);

							}
						}
					} 
			}
		} else {
			$erro = 'Não há imagens para $nomesearch';
		}
	}
	@mysql_free_result($res);
	if (!empty($nomesciid2)) {
		$nn = explode("_",$nomesciid2);
		$tipo = $nn[0];
		$id = $nn[1];
		if ($tipo=='infspid') { $qu =  "InfraEspecieID='".$id."'";}
		if ($tipo=='speciesid') { $qu = "EspecieID='".$id."'";}
		if ($tipo=='genusid') { $qu = "GeneroID='".$id."'";}
		if ($tipo=='famid') { $qu = "FamiliaID='".$id."'";}

		$qq = "(SELECT Especimenes.EspecimenID,NULL as PlantaID,TraitVariation,Traits.TraitName,Especimenes.DetID,CONCAT(Abreviacao,' ',Number) as ImgRef,NULL as ImgRef2 FROM Especimenes JOIN Identidade USING(DetID) JOIN Traits_variation ON Especimenes.EspecimenID=Traits_variation.EspecimenID JOIN Traits ON Traits.TraitID=Traits_variation.TraitID JOIN Pessoas ON Especimenes.ColetorID=Pessoas.PessoaID WHERE TraitTipo='Variavel|Imagem' AND Identidade.".$qu.")";

		$qq = $qq." UNION (SELECT NULL as EspecimenID,Plantas.PlantaID,TraitVariation,Traits.TraitName,Plantas.DetID,PlantaTag as ImgRef1,InSituExSitu as ImgRef2 FROM Plantas JOIN Identidade USING(DetID) JOIN Traits_variation ON Plantas.PlantaID=Traits_variation.PlantaID JOIN Traits ON Traits.TraitID=Traits_variation.TraitID WHERE TraitTipo='Variavel|Imagem' AND Identidade.".$qu.")";

		$res = mysql_query($qq,$conn);
		$nres = mysql_numrows($res);
		if ($nres>0) {
			$imagens2=1;
			$uid = $_SESSION['userid'];

			$qq = "DROP TABLE temp_image2_".$uid;
			@mysql_query($qq,$conn);

			$tbname = "temp_image2_".$uid;
			$qq = "CREATE TABLE ".$tbname." (
				TempID INT(10) unsigned NOT NULL auto_increment,
				PlantaID INT(10),
				EspecimenID INT(10),
				DetID INT(10),
				TraitName VARCHAR(100),
				TaxaNome VARCHAR(100),
				ImageID INT(10),
				ImgRef CHAR(100),
				AddedBy INT(10), 
				AddedDate DATE,
				PRIMARY KEY (TempID))";
			mysql_query($qq,$conn);

			while ($row = mysql_fetch_assoc($res)) {
					$especimenid = $row['EspecimenID'];
					$plantaid = $row['PlantaID'];
					$imgids = explode(";",$row['TraitVariation']);
					$tid = $row['TraitName'];
					$detid = $row['DetID']+0;
					$ref = $row['ImgRef'];
					$ref2 = $row['ImgRef2'];
					if ($plantaid>0) {
						//taxonomy
						$tgn = sprintf("%05s",$ref+0);
						$pp='Tag: ';
						if ($ref2=='Insitu') { $pp = $pp."JB-N-";}
						if ($ref2=='Exsitu') { $pp = $pp."JB-X-";}
						$ref = $pp.$tgn;
					}
					if ($detid>0) {
						$nomenoautor = getdetnoautor($detid,$conn);
					}

					if (count($imgids)>0) {
						foreach ($imgids as $img) {
							$img = $img+0;
							if ($img>0) {
								$arrayofvalues = array(
								'PlantaID' => $plantaid,
								'EspecimenID' => $especimenid,
								'DetID' => $detid,
								'TraitName' => $tid,
								'TaxaNome' => $nomenoautor,
								'ImgRef' => $ref,
								'ImageID' => $img+0);
							$newdetid = InsertIntoTable($arrayofvalues,'TempID',$tbname,$conn);
							}
						}
					} 
			}
		} else {
			$erro = 'Não há imagens para $nomesearch2';
		}
	}
}

$title = 'Mostra Imagens por Espécie';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//tabelas temporarias com as imagens 
if ($imagens2==1 && empty($imagens1)) {
	$tbname = "temp_image2_".$uid;
	$imagens1=1;
	$nomesearch = $nomesearch2;
	unset($imagens2);
} else {
	$tbname = "temp_image_".$uid;
}

//CABEÇALHO
if ($imagens2==1 || $imagens1==1) {
	echo "
<br />
<table cellpadding='5'>
  <tr>";
	if ($imagens1==1) {
		echo "
    <td colspan='2' align='center' style='font-size: 1.5em;'><i>".$nomesearch."</i></td>";
	}
	if ($imagens2==1) {
		echo "
    <td>&nbsp;</td>
    <td colspan='2' style='font-size: 1.5em;' align='center'><i>".$nomesearch2."</i></td>";
	}
	echo "
  </tr>
  <tr>";

}
//IMAGEM 01
if ($imagens1==1) {
	$dirsmall = 'img/thumbnails/';
	$dirmedium = 'img/lowres/';
	$dirlarge = 'img/copias_baixa_resolucao/';
	$diroriginal = 'img/originais/';

	$qq = "SELECT * FROM ".$tbname." JOIN Imagens ON ".$tbname.".ImageID=Imagens.ImageID ORDER BY TaxaNome ASC";
	$res = mysql_query($qq,$conn);
	$ii = 0;
echo "
      <td>
          <div class='imagemultiple'>
            <br />";
	while ($rw = mysql_fetch_assoc($res)) {
		$plantaid_run = $rw['PlantaID'];
		$specimenid_run = $rw['EspecimenID'];
		$detset = getdetsetvar($rw['DetID'],$conn);
		$detset = serialize($detset);
		$dettext = describetaxa($detset,$conn);
		$tname  =$rw['TraitName'];
		$filename = $rw['FileName'];
		$taxanome = "<i>".$rw['TaxaNome']."</i>";
		$ref = $rw['ImgRef'];
		$label = $taxanome."<br />".$ref."<br />(".$tname.")";
		$dettext = $dettext."<br />[Voucher - ".$ref."]";
		if ($ii==0) {
			$firstimgname = $rw['FileName'];
			$plantaid = $rw['PlantaID'];
			$specimenid = $rw['EspecimenID'];
			$detid = $rw['DetID'];
			$firstdettext = $dettext;
		}
		$ii++;
		echo "
            <a  href='".$dirlarge.$filename."' rel='zoom-id:Zoomer;zoom-position:inner' rev='".$dirmedium.$filename."' onclick=\"javascript:chang_det('".$plantaid_run."','".$specimenid_run."','".$dettext."','img1');\"  >
                <img style='border: thin solid gray;' src='".$dirsmall.$filename."' alt='".$label."' />
              </a>
              <br />
              ".$label."
              <br />
              <br />
              ";
}
echo "
          </div>
        </td>
        <td>
<table align='left'>
<tr><td id='dettexto_img1' align='left'>".$firstdettext."</td></tr>
<tr><td>
  <table align='rigth'>
      <tr>
        <td align='center'>
        <input type='hidden' id='detid' value='$detid' name='detid' />
        <input type='hidden' id='pl_img1' name='plantaid' value='".$plantaid."' />
        <input type='hidden' id='sp_img1' name='specimenid'  value='".$specimenid."' />
        <input type='button'  style='padding: 5px;' value='Mudar Determinação' ";
		$myurl ="taxonomia-popup.php?ispopup=1&dettextid=dettexto&getplspid=img1&saveit=true"; 
		echo "  onclick = \"javascript:small_window('$myurl',800,400,'Identificando amostra 01');\" /></td>
        <td align='center'><input type='button' style='padding: 5px' value='Histórico' ";
		$myurl ="detchangespopup.php?ispopup=1&getplspid=img1"; 
		echo "  onclick = \"javascript:small_window('$myurl',800,300,'Det History');\" /></td>
      </tr>
   </table>
  </td>
</tr>
<tr>
  <td>
<div class='imagecontainer'>
            <a  href='".$dirlarge.$firstimgname."' rel='zoom-position:inner'  class='MagicZoomPlus' id='Zoomer' title='Zoom'>
              <img style='border: thin solid gray;' src='".$dirmedium.$firstimgname."'/>
            </a>
            <br />
            <!---<a  style='vertical-align: bottom; align: left; font-size: 0.9em;' href='".$diroriginal.$firstimgname."' target='_new'>Imagem de melhor resolução</a> --->
            <input type='button' value='Imagem de melhor resolução' onclick=\"javascript:gethrefvalue('Zoomer');\" />
          </div>
        </td>
</tr>
</table>
</td>";
}


if ($imagens2==1) {
	$dirsmall = 'img/thumbnails/';
	$dirmedium = 'img/lowres/';
	$dirlarge = 'img/copias_baixa_resolucao/';
	$diroriginal = 'img/originais/';

	$tbname = "temp_image2_".$uid;
	$qq = "SELECT * FROM ".$tbname." JOIN Imagens ON ".$tbname.".ImageID=Imagens.ImageID ORDER BY TaxaNome ASC";
	$res = mysql_query($qq,$conn);
	$ii = 0;
echo "
        <td>&nbsp;</td>
        <td>
          <div class='imagemultipleright'>
            <br />";
	while ($rww = mysql_fetch_assoc($res)) {
		$plantaid_run = $rww['PlantaID'];
		$specimenid_run = $rww['EspecimenID'];
		$detset = getdetsetvar($rww['DetID'],$conn);
		$detset = serialize($detset);
		$dettext = describetaxa($detset,$conn);
		$tname  =$rww['TraitName'];
		$filename = $rww['FileName'];
		$taxanome = "<i>".$rww['TaxaNome']."</i>";
		$ref = $rww['ImgRef'];
		$label = $taxanome."<br />".$ref."<br />(".$tname.")";
		$dettext = $dettext."<br />[Voucher - ".$ref."]";
		if ($ii==0) {
			$firstimgname = $rww['FileName'];
			$plantaid = $rww['PlantaID'];
			$specimenid = $rww['EspecimenID'];
			$detid = $rww['DetID'];
			$firstdettext2 = $dettext;
		}
		$ii++;
		$label = $taxanome."<br />".$ref."<br />(".$tname.")";
		echo "
            <a  href='".$dirlarge.$filename."' rel='zoom-id:newframe;zoom-position:inner' rev='".$dirmedium.$filename."' onclick=\"javascript:chang_det('".$plantaid_run."','".$specimenid_run."','".$dettext."','img2');\"  >
              <img style='border: thin solid gray;' src='".$dirsmall.$filename."'/ alt='".$label."' />
            </a>
            <br />
            ".$label."
            <br />
            <br />";
}
echo "
          </div>
        </td>
        <td>
<table align='left'>
<tr><td id='dettexto_img2' align='left'>".$firstdettext2."</td></tr>
<tr><td>
  <table align='rigth'>
      <tr>
        <td align='center'>
        <input type='hidden' id='pl_img2' name='plantaid2' value='".$plantaid."' />
        <input type='hidden' id='sp_img2' name='specimenid2'  value='".$specimenid."' />
        <input type='button'  style='padding: 5px;' value='Mudar Determinação' ";
		$myurl ="taxonomia-popup.php?ispopup=1&dettextid=dettexto2&getplspid=img2&saveit=true"; 
		echo "  onclick = \"javascript:small_window('$myurl',800,400,'Identificando amostra 01');\" /></td>
        <td align='center'><input type='button' style='padding: 5px' value='Histórico' ";
		$myurl ="detchangespopup.php?ispopup=1&getplspid=img2"; 
		echo "  onclick = \"javascript:small_window('$myurl',800,300,'Det History');\" /></td>
      </tr>
   </table>
  </td>
</tr>
<tr>
        <td>
          <div class='imagecontainerright'>
            <a  href='".$dirlarge.$firstimgname."'  rel='zoom-position:inner' class='MagicZoomPlus' id='newframe' title='Zoom'>
              <img style='border: thin solid gray;' src='".$dirmedium.$firstimgname."'/>
            </a>
            <br />
            <!---<a  style='vertical-align: bottom; align: left; font-size: 0.9em;' href='".$diroriginal.$firstimgname."' target='_new'>Imagem de melhor resolução</a> --->
            <input type='button' value='Imagem de melhor resolução' onclick=\"javascript:gethrefvalue('newframe');\" />
          </div>
        </td>";
}

if ($imagens2==1 || $imagens1==1) {
	echo "
      </tr>
    </table>
  </br>";
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>