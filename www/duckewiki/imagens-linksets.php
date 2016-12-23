<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

$gget = cleangetpost($_GET,$conn);
@extract($gget);

//echopre($gget);
$dirthumb = 'img/thumbnails/';
$dirmedium = 'img/lowres/';
$dirlarge = 'img/copias_baixa_resolucao/';
$diroriginal = 'img/originais/';
$baseqq  = "SELECT 
pltb.EspecimenID, 
CONCAT(colpessoa.SobreNome,' ', pltb.Number) as COLETOR_NO, 
famtb.Familia as FAMILIA,
gettaxonname(pltb.DetID,1,0) as NOME,
(IF(pltb.GPSPointID>0,gazgps.PathName,IF(pltb.GazetteerID>0,gaz.PathName,' '))) as LOCAL
FROM Especimenes as pltb 
LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID 
LEFT JOIN Identidade as iddet USING(DetID) 
LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID 
LEFT JOIN Gazetteer as gaz ON gaz.GazetteerID=pltb.GazetteerID  
LEFT JOIN GPS_DATA as gpspt ON gpspt.PointID=pltb.GPSPointID  
LEFT JOIN Gazetteer as gazgps ON gpspt.GazetteerID=gazgps.GazetteerID  
";
$baseorder = "ORDER BY FAMILIA,gettaxonname(pltb.DetID,1,0), colpessoa.SobreNome, pltb.Number";

$ids = explode("_",$ids);

if (!isset($_SESSION['imagelinking']) || !isset($imageindex)) {
	$qq = "SELECT * FROM Imagens WHERE AddedBy=".$ids[0]." AND AddedDate='".$ids[1]."' AND UnLinked=1 ORDER BY DateOriginal,TimeOriginal";
	$res = mysql_query($qq,$conn);
	$imagelinking = array();
	while($row = mysql_fetch_assoc($res)) {
		$imagelinking[] = $row['ImageID'];
   }
   $_SESSION['imagelinking']= serialize($imagelinking);
} else {
	$imagelinking = unserialize($_SESSION['imagelinking']);
}

#se nao estiver avançando
if (!isset($imageindex)) {
	$imageindex = 0;
}

if (count($imagelinking)>0) {
	$curimageid = $imagelinking[$imageindex];
	$qq = "SELECT * FROM Imagens WHERE ImageID=".$curimageid;
	$res = mysql_query($qq,$conn);
	$curimage = mysql_fetch_assoc($res);




$txt = "<div class='tabela'  id=\"imagedisplay\" style=\"display: table;\">";
//while($row = mysql_fetch_assoc($res)) {
$filename = $curimage["FileName"];
$imgid = $curimage['ImageID'];
$imgdate = $curimage["DateOriginal"];


$imgdd = explode("-",$imgdate);
	//echo $dirlarge.$filename;
$thetraitid = $curimage["TraitID"];
$ospecid =0;
if ($thetraitid>0) {
	$sqll = "SELECT * FROM Traits_variation WHERE TraitID=".$thetraitid." AND (TraitVariation LIKE '".$imgid."' OR TraitVariation LIKE '%;".$imgid."' OR TraitVariation LIKE '%;".$imgid.";%') ";
	$ores3 = mysql_query($sqll,$conn);
	$temja= mysql_num_rows($ores3);	
	if ($temja) {
		$tem = mysql_fetch_assoc($ores3);
		$ospecid = $tem['EspecimenID'];
	} else {
		$ospecid = $especimenid;	
	}
} else {
		$ospecid = $especimenid;	
}
$txt .= "
<div style=\"display: table-row;\">
<div style=\"display: table-cell; padding: 10px; \">
<a href=\"".$diroriginal.$filename."\" title=\"".$filename."\" data-spzoom data-spzoom-width=\"400\" data-spzoom-height=\"400\"><img src=\"".$dirlarge.$filename."\" alt=\"".$filename."\" width=\"300px\" /></a>
</div>
<div style=\"display: table-cell; vertical-align: middle; padding: 10px;\">
<input type='hidden' value='".$imgid."' id='curimageid' >
<select id='thespecid' name='especimenid_".$imgid."' onchange=\"javascript: mudasample('showex_".$imgid."',this,".$imgid.");\" >
  <option value=''>Amostras do dia da imagem</option>";
 
if ($days>0) {
$date1 = date("Y-m-d",strtotime($imgdate." -".$days." days"));
$date2 = date("Y-m-d",strtotime($imgdate." +".$days." days"));
$qwhere = " WHERE DATE(CONCAT(pltb.Ano,\"-\",pltb.Mes,\"-\",pltb.Day))>=DATE('".$date1."') AND 
DATE(CONCAT(pltb.Ano,\"-\",pltb.Mes,\"-\",pltb.Day))<=DATE('".$date2."') ";
} else {
$qwhere = " WHERE DATE(CONCAT(pltb.Ano,\"-\",pltb.Mes,\"-\",pltb.Day))=DATE('".$imgdate."') ";
}

  
  $sql = $baseqq.$qwhere.$baseorder;
  //echo $sql;  
  $resaa = mysql_query($sql,$conn);
while ($aa = mysql_fetch_assoc($resaa)){
	if ($aa["EspecimenID"]==$ospecid) {$sel="selected";} else {$sel="";}
		   $txt .= "
  <option ".$sel." value='".$aa['EspecimenID']."'>".$aa['COLETOR_NO']." [".$aa['FAMILIA']." ".$aa['NOME']."] [.".$aa['LOCAL']."]</option>";
	}
	 $txt .= "
</select>
<br />
<select id='traitsel' name='traitid_".$imgid."'>
<option value=''>".GetLangVar('nameselect')." Variável</option>";
	$filtro ="SELECT * FROM Traits WHERE TraitTipo='Variavel|Imagem' ORDER BY TraitName";
	$resaa = mysql_query($filtro,$conn);
	while ($aa = mysql_fetch_assoc($resaa)){
		$vt = $curimage["TraitID"];
		if ($vt==$aa["TraitID"]) { $sel = "selected";} else { $sel="";}
		   $txt .= "
		   <option $sel value='".$aa['TraitID']."'>".$aa['TraitName']."</option>";
	}
	$txt .= "
</select>
<div id='showex_".$imgid."'></div>
</div>
</div>
<div style=\"display: table-row;\">";
$nimgs = count($imagelinking);
if ($imageindex>0 && $imageindex<=$nimgs){
$txt .= "<input type=\"button\" value=\"<\" style=\"font-size: 1.5em;\" onclick=\"javascript:mudaset(".($imageindex-1).");\" />";
}
$txt .= "&nbsp;".$filename."&nbsp;";

if ($imageindex<$nimgs){
$txt .= "<input type=\"button\" value=\">\" style=\"font-size: 1.5em;\" onclick=\"javascript:mudaset(".($imageindex+1).");\" />";
}
$txt .= "
</div>
</div>
";
echo $txt;
}
?>