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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}

$which_css = array(
"<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/geral.css\" />",
"<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/cssmenu.css\" />",
"<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"javascript/magiczoomplus/magiczoomplus/magiczoomplus.css\" />"
);
$which_java = array(
"<script type=\"text/javascript\" src=\"css/cssmenuCore.js\"></script>",
"<script type=\"text/javascript\" src=\"css/cssmenuAddOns.js\"></script>",
"<script type=\"text/javascript\" src=\"css/cssmenuAddOnsItemBullet.js\"></script>",
"<script type=\"text/javascript\" src=\"javascript/magiczoomplus/magiczoomplus/magiczoomplus.js\"></script>"
);
$title = 'Identifica por Imagem';
$body = '';
$nimgs =0;
//prep the data
if ($filtro>0 && !isset($changed)) { 
  $qq = "(SELECT Especimenes.EspecimenID,NULL as PlantaID,TraitVariation,Traits.TraitName,Especimenes.DetID,CONCAT(Abreviacao,' ',Number) as ImgRef,NULL as ImgRef2,TraitTipo FROM Especimenes JOIN Identidade USING(DetID) JOIN Traits_variation ON Especimenes.EspecimenID=Traits_variation.EspecimenID JOIN Traits ON Traits.TraitID=Traits_variation.TraitID JOIN Pessoas ON Especimenes.ColetorID=Pessoas.PessoaID WHERE TraitTipo LIKE '%Imagem' AND (Especimenes.FiltrosIDS LIKE '%filtroid_".$filtro.";%' OR Especimenes.FiltrosIDS LIKE '%filtroid_".$filtro."'))";
  $qq = $qq." UNION (SELECT NULL as EspecimenID,Plantas.PlantaID,TraitVariation,Traits.TraitName,Plantas.DetID,PlantaTag as ImgRef1,InSituExSitu as ImgRef2,TraitTipo FROM Plantas JOIN Identidade USING(DetID) JOIN Traits_variation ON Plantas.PlantaID=Traits_variation.PlantaID JOIN Traits ON Traits.TraitID=Traits_variation.TraitID WHERE TraitTipo LIKE '%Imagem' AND (Plantas.FiltrosIDS LIKE '%filtroid_".$filtro.";%' OR Plantas.FiltrosIDS LIKE '%filtroid_".$filtro."' ))";
  $res = mysql_query($qq,$conn);
  $nres = mysql_numrows($res);
  $nimgs = $nres;
  if ($nres>0) {
	$imagens1=1;
	$uid = $_SESSION['userid'];
	$tbname = "temp_idbyimage_".$uid; 
	$qq = "DROP TABLE ".$tbname;
	@mysql_query($qq,$conn);
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
		$qq  = "ALTER TABLE ".$tbname." ORDER BY TaxaNome,ImgRef ASC";
		mysql_query($qq,$conn);
	} 
	mysql_free_result($res);
} 
elseif (!isset($filtro)) {
	header("location: identifybyimg-form.php?ispopup=1");
} 
else { 
	$uid = $_SESSION['userid'];
	$tbname = "temp_idbyimage_".$uid; 
	//atualiza tabela temporaria se identificacao mudou
	if ($changed==1 && $detid<>$olddetid) {
		if ($detid>0) {
			$oldspecid = $oldspecid+0;
			$oldplid = $oldplid+0;
			if ($oldspecid>0 && $oldplid==0) {
				$qu = "EspecimenID='".$oldspecid."'";
			}
			if ($oldplid>0 && $oldspecid==0) {
				$qu = "PlantaID='".$oldplid."'";
			}
			$qq = "UPDATE ".$tbname." SET DetID='".$detid."' WHERE ".$qu;
			mysql_query($qq,$conn);
		}
	}
	$qq = "SELECT * FROM ".$tbname." WHERE ImgRef='".$tempid."' LIMIT 0,1";
	$rs = mysql_query($qq,$conn);
	$nres = mysql_numrows($rs);
	$nimgs = $nres;
	if ($nimgs>0) {
		$imgrw = mysql_fetch_assoc($rs);
		$specid = $imgrw['EspecimenID']+0;
		$plid = $imgrw['PlantaID']+0;
	}
	/////
}


FazHeader($title,$body,$which_css,$which_java,$menu);
if ($nimgs>0) {
	$dirsmall = 'img/thumbnails/';
	$dirmedium = 'img/lowres/';
	$dirlarge = 'img/copias_baixa_resolucao/';
	$diroriginal = 'img/originais/';

	$uid = $_SESSION['userid'];
	$tbname = "temp_idbyimage_".$uid; 

	unset($qu);
	if ($specid>0) {
		$qu = " WHERE EspecimenID ='".$specid."'";
	}
	if ($plid>0) {
		$qu = " WHERE PlantaID ='".$plid."'";
	}
	if (empty($qu)) {
		$qu = "LIMIT 0,1";
	}
	$qq = "SELECT * FROM ".$tbname." JOIN Imagens ON ".$tbname.".ImageID=Imagens.ImageID ".$qu;
	//echo $qq."<br>";
	$res = mysql_query($qq,$conn);
	$ii = 0;
	while ($imgrow = mysql_fetch_assoc($res)) {
		if ($ii==0) {
			$olddetid = $imgrow['DetID'];
			$dettaxa = getdet($olddetid,$conn);
			$detnome = $dettaxa[0];
			$detdetby = trim($dettaxa[1]);
			$familia = strtoupper(trim($dettaxa[2]));
			
			$detset = getdetsetvar($detid,$conn);
			$detset = serialize($detset);
			$dettext = describetaxa($detset,$conn);
			$dettext = $familia."  ".$detnome;
			if (!empty($detdetby)) { $dettext =$dettext." <br />Det por: ".$detdetby.")";}
			$nomenoautor = getdetnoautor($olddetid,$conn);
			$oldplid = $imgrow['PlantaID'];
			$oldspecid = $imgrow['EspecimenID'];
			echo "
<br />
<table cellpadding='5'>
<tr>
  <td colspan='100%'>
  <table align='left'>
    <tr>
      <td id='dettexto' align='left'>".$dettext."</td>
      <td>&nbsp;</td>
      <td>
        <table align='rigth'>
        <tr>
<td align='center'><input type='button'  style='padding: 2px' value='Mudar Det' class='bsubmit' ";
$myurl ="taxonomia-popup.php?ispopup=1&detid=$olddetid&dettextid=dettexto&especimenid=".$oldspecid."&plantaid=".$oldplid."&saveit=true"; 
echo "  onclick = \"javascript:small_window('$myurl',800,400,'Identificando');\" />
</td>
<td align='center'><input type='button' style='padding: 2px' value='Histórico' class='bblue' ";
$myurl ="detchangespopup.php?ispopup=1&especimenid=".$oldspecid."&plantaid=".$oldplid; 
echo "  onclick = \"javascript:small_window('$myurl',800,300,'Det History');\" />
</td>
        </tr>
        </table>
      </td>
    </tr>
    </table>
  </td>
</tr>
<tr><td>
  <div class='imagemultiple'>";
      $firstimgname = $imgrow['FileName'];
	}
		$ii++;
		$tname  =$imgrow['TraitName'];
		$filename = $imgrow['FileName'];
		$taxanome = "<i>".$imgrow['TaxaNome']."</i>";
		$ref = $imgrow['ImgRef'];
		$label = $taxanome."<br />".$ref."<br />(".$tname.")";
		echo "
<br />
<a  href=\"".$dirlarge.$filename."\" rel=\"zoom-id:Zoomer;zoom-position:inner\" rev=\"".$dirmedium.$filename."\">
<img style=\"border: thin solid gray;\" src=\"".$dirsmall.$filename."\" alt=\"".$label."\" /></a>
<br />".$label."<br />";
	}

echo "
    </div>
  </td>
  <td>&nbsp;</td>
  <td><div class='imagecontainer'><a  href='".$dirlarge.$firstimgname."' rel='zoom-position:inner'  class='MagicZoomPlus' id='Zoomer' title='Zoom'>
<img style='border: thin solid gray;' src='".$dirmedium.$firstimgname."'/></a><br />
<!---<a  style='vertical-align: bottom; align: left; font-size: 0.9em;' href='".$diroriginal.$firstimgname."' target='_new'>
Imagem de melhor resolução</a> ---><input type='button' value='Imagem de melhor resolução' onclick=\"javascript:gethrefvalue('Zoomer');\" /></div>
  </td>
</tr>
<tr>
<form method='post' action='identifybyimg-exec.php'>
<input type='hidden' id='detid' name='detid' value='$detid' >
<input type='hidden' name='olddetid' value='$olddetid' >
<input type='hidden' name='oldspecid' value='$oldspecid' >
<input type='hidden' name='oldplid' value='$oldplid' >
<input type='hidden' name='changed' value='1' >
<input type='hidden' name='filtro' value='".$filtro."' >
<input type='hidden' name='ispopup' value='".$ispopup."' >
  <td colspan='100%'>
    <table align='center'>
      <tr><td>Outras imagens no filtro selecionado: </td>
      <td>
        <select name='tempid' onchange='this.form.submit();'>";
		$qq = "SELECT DISTINCT ImgRef,TaxaNome FROM ".$tbname." ORDER BY TaxaNome,ImgRef";
		$res = mysql_query($qq,$conn);
		while ($rew = mysql_fetch_assoc($res)) { 
			$ref = $rew['ImgRef'];
			$ttid = $rew['TempID'];
			$nome = $rew['TaxaNome'];
			if ($ref==$tempid) {
				echo "
          <option selected value='".$ref."'>".$ref." (".$nome.")</option>";
			} else {
				echo "
          <option value='".$ref."'>".$ref." (".$nome.")</option>";
			}
		}
echo "
        </select>
      </td>
    </tr>
  </table>
  </td>
</form>
</tr>
</table>
";

}
else {
	echo "<div align='center'><p class='erro' style='padding: 5px'>Não há imagens associadas ao filtro selecionado</p></div>";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>