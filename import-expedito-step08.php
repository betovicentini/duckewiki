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
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Importar Expedito 08';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


$nnv = $_SESSION['fieldsign'];
$newv = unserialize($nnv);
if (!isset($pontoexpedito)) {
	$colsign = array(
	"PTID",
	"LOCALIDADE_ESPECIFICA",
	"LONGITUDE_PONTOGPS",
	"LATITUDE_PONTOGPS");
	$pontoexpedito = array();
	foreach ($colsign as $kk) {
		$datalev = trim($newv[$kk]);
		if (!empty($datalev)) {
			$pontoexpedito[$kk] = $datalev;
		}
	}
} 
$longf = trim($newv["LONGITUDE_PONTOGPS"]);
$latf = trim($newv["LATITUDE_PONTOGPS"]);
$cll=  $tbprefix."LONGITUDE_PONTOGPS";
$cll2=  $tbprefix."LATITUDE_PONTOGPS";

$intervalo = trim($newv["INTERVALO"]);
$cll3=  $tbprefix."IntervaloTempo";



$dataproblems = array();
if (!empty($longf) && !empty($latf)) {
	$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cll." FLOAT DEFAULT NULL,  ADD COLUMN ".$cll2." FLOAT DEFAULT NULL";
	@mysql_query($qq,$conn);
	$qq = "UPDATE `".$tbname."` SET `".$cll."`=checkcoordenadas(`".$longf."`,'LONGITUDE') WHERE `".$longf."`<>'' AND `".$longf."` IS NOT NULL AND `".$cll."` IS NULL";
	mysql_query($qq,$conn);

	$qq = "UPDATE `".$tbname."` SET `".$cll2."`=checkcoordenadas(`".$latf."`,'LATITUDE') WHERE `".$latf."`<>'' AND `".$latf."` IS NOT NULL AND `".$cll2."` IS NULL";
	mysql_query($qq,$conn);

	$qq = "SELECT * FROM `".$tbname."` WHERE `".$longf."`<>'' AND `".$longf."` IS NOT NULL AND `".$cll."` IS NULL";
	$rr = mysql_query($qq,$conn);
	$nr = mysql_numrows($rr);
	if ($nr>0) {
		$dataproblems[$longf] = array("Os valores nessa coluna não correspondem a valores de LONGITUDE (em décimos de grau)","latlong","LONGITUDE");
	}
	$qq = "SELECT * FROM `".$tbname."` WHERE `".$latf."`<>'' AND `".$latf."` IS NOT NULL AND `".$cll2."` IS NULL";
	$rr = mysql_query($qq,$conn);
	$nr = mysql_numrows($rr);
	if ($nr>0) {
		$dataproblems[$latf] = array("Os valores nessa coluna não correspondem a valores de LATITUDE (em décimos de grau)","latlong","LATITUDE");
	}
}    


if (!empty($intervalo)) {
	$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cll3." INT DEFAULT 0";
	@mysql_query($qq,$conn);
	$qq = "UPDATE `".$tbname."` SET `".$cll3."`=IF(IsInteger(`".$intervalo."`),TRIM(`".$intervalo."`)+0,0) WHERE IsInteger(`".$intervalo."`)=1 AND `".$intervalo."`<>'' AND `".$intervalo."` IS NOT NULL AND `".$cll3."`=0";
	mysql_query($qq,$conn);

	$qq = "SELECT * FROM `".$tbname."` WHERE `".$intervalo."`<>'' AND `".$intervalo."` IS NOT NULL AND `".$cll3."`=0";
	//echo $qq."<br />";
	$rr = mysql_query($qq,$conn);
	$nr = mysql_numrows($rr);
	if ($nr>0) {
		$dataproblems[$intervalo] = array("Os valores de intervalo nessa coluna devem ser números inteiros. Por definição cada intervalo corresponde a 15 minutos de inventário",'intervalo');
	}
}    
if (count($dataproblems)>0) {
echo "<form action='import-expedito-step08.php' method='post'>
<br />
<table align='center' class='myformtable' cellpadding='5'>
<thead>
 <tr><td colspan='100%'>Os seguintes erros foram encontrados nas coordenadas geográficas e/ou intervalo de tempo<td></tr>
  <tr class='subhead'>
  <td>Coluna</td>
  <td>Erro</td>
  <td>O que fazer?</td>
 </tr>
</thead>
<tbody>";
	foreach ($ppost as $kk => $vv) {
	if (!empty($vv)) {
		echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
	}
}
foreach ($dataproblems as $orgcol => $vv) {
	if ($orgcol==$longf) {
		$cln = $cll;
	} elseif ($orgcol==$latf) {
		$cln = $cll2;
	}
	if ($orgcol==$intervalo) {
		$cln = $cll3;
	}
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'><td>$orgcol</td><td>".$vv[0]."</td>
  <td align='center'>
    <input id='butidx' type='button' style=\"font-size:90%;background-color:#0066CC; color: white;border: thin outset gray;padding: 0.1em\" value='Corrigir' ";
	$myurl ="checkdatas-popup.php?colname=".$cln."&orgcol=".$orgcol."&tbname=".$tbname."&buttonidx=butidx&datatipo=".$vv[1];
	if (!empty($vv[2])) { $myurl .= "&latlonglink=".$vv[2];}  
	echo " onclick = \"javascript:small_window('$myurl',800,400,'Corrigir valores em alguns campos');\">
  </td>
  </tr>"; 

}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center' colspan='100%'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td>
</tr>
</tbody>
</table>
</form>";
} 
else {
	$coordinateschecked= TRUE;
}

$_SESSION['fieldsign'] = serialize($newv);

if ($coordinateschecked) {
echo "
<form name='myform' action='import-expedito-step09.php' method='post'>
";
//coloca as variaveis anteriores
	foreach ($ppost as $kk => $vv) {
	echo "
  	<input type='hidden' name='".$kk."' value='".$vv."' />"; 
	}
//echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
echo "
  <table cellpadding=\"1\" width='50%' align='center'>
    <tr><td class='tdsmallbold' align='center'><input type='submit' value='continuar' class='bsubmit' /></td></tr>
  </table> 
 </form>";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>