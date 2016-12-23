<?php
//este script checa images importadas ao banco de dados mas que nao foram relacionadas com nada e permite criar uma relacao, buscando relacoes que tem a mesma data
//permite ligar com uma amostra coletada, com uma planta marcada ou com um habitat
//precisa modificar o script para fazer outros tipos de relacao que nao foram ainda implementados
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

$dirthumb = 'img/thumbnails/';
$dirmedium = 'img/lowres/';
$dirlarge = 'img/copias_baixa_resolucao/';
$diroriginal = 'img/originais/';
//echopre($gget);
$sql = "SELECT * FROM Traits_variation WHERE EspecimenID=".$especimenid."  AND TraitID=".$exsicatatrait;
$res = mysql_query($sql,$conn);
$nres = mysql_num_rows($res);
if ($nres>0) {
	$row= mysql_fetch_assoc($res);
	$rr = $row['TraitVariation'];
   $rr = explode(";",$rr);
   $id = $rr[0];
   $qq = "SELECT * FROM Imagens WHERE ImageID=".$id;
   $ores = mysql_query($qq,$conn);
	$orow= mysql_fetch_assoc($ores);
   $filename = $orow["FileName"];
   $txt = "
<a href=\"".$dirlarge.$filename."\" title=\"".$filename."\" data-spzoom data-spzoom-width=\"400\" data-spzoom-height=\"400\"><img src=\"".$dirmedium.$filename."\" alt=\"".$filename."\" width=\"280px\" /></a>";
} else {
   $txt = "Sem imagem de exsicata";
}
//echo $txt;
//checa se a imagem já está relacionada à esta amostra
if ($thetraitid>0 && $imgid>0){
$sql = "SELECT * FROM Traits_variation WHERE EspecimenID=".$especimenid."  AND TraitID=".$thetraitid." AND (TraitVariation LIKE '".$imgid."' OR TraitVariation LIKE '%;".$imgid."' OR TraitVariation LIKE '%;".$imgid.";%') ";
$ores2 = mysql_query($sql,$conn);
$nlinks= mysql_num_rows($ores2);
	if ($nlinks>0) {
		$linkref = "RELAÇÃO SALVA";
	} else {
		$sqll = "SELECT * FROM Traits_variation WHERE TraitID=".$thetraitid." AND (TraitVariation LIKE '".$imgid."' OR TraitVariation LIKE '%;".$imgid."' OR TraitVariation LIKE '%;".$imgid.";%') ";
		$ores3 = mysql_query($sqll,$conn);
		$temoutra= mysql_num_rows($ores3);		
		if ($temoutra>0) {
			$linkref = "JÁ RELACIONADA COM OUTRA AMOSTRA";		
		} else {
			$linkref = "NÃO RELACIONADA";
			$relacionar = "<input type='button' value='Salvar esta relação' onclick=\"salvarelacao(".$imgid.",".$especimenid.");\" >";
			$linkref = $linkref."&nbsp;".$relacionar;
		}
	}
} else {
	$linkref = "VARIÁVEL NÃO DEFINIDA";
}
$imagen="<span style='font-size: 0.6em;'>".$specname."</span><br >
<img style='cursor:pointer;' src='icons/edit-notes.png' height='30' onclick=\"javascript:small_window('showspecimen.php?ispopup=1&especimenid=".$especimenid."',400,400,'Notas');\" >&nbsp;Notas";
//$imgg = "<img style='cursor:pointer;' src='icons/label-icon.png' height='30' onclick=\"javascript:small_window('singlelabel-exec.php?ispopup=1&specimenid=".$especimenid."',300,100,'Imprimindo Etiqueta');\" >";

$otxt = "<table><tr><td>".$txt."</td><td style='vertical-align: top;' ><span id='linkref' style='color: red; font-weight: bold;' >".$linkref."</span><br>".$imagen."<br /><br />".$imgg."</td></tr></table>";

echo $otxt;
?>