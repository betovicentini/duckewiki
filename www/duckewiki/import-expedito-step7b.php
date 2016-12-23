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
$coltaxs = array(
$tbprefix."InfraEspecieID" => 'infspid_',
$tbprefix.'EspecieID' => 'speciesid_',
$tbprefix.'GeneroID' => 'genusid_',
$tbprefix.'FamiliaID' => 'famid_'
);
$actualcolls = array();
foreach ($coltaxs as $kk => $vv) {
	$qu = "SHOW FIELDS FROM `".$tbname."` WHERE Field LIKE '".$kk."'";
	$ru = mysql_query($qu,$conn);
	$nru = mysql_numrows($ru);
	if ($nru>0) {
		$actualcolls[$kk] = $vv;
	}
}
if (count($actualcolls)>0) {
	$coltoadd  = $tbprefix."TaxonomiaIDs";
	$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$coltoadd." VARCHAR(100) DEFAULT ''";
	@mysql_query($qq,$conn);
	$qq = "UPDATE ".$tbname." as tb SET ".$coltoadd."=";
	foreach ($actualcolls as $kk => $vv) {
		$qq .="IF (tb.".$kk.">0,CONCAT('".$vv."',tb.".$kk."), ";
	}
	$qq .="''))))";
	if ($temtestemunho==1) {
		$qq .= " WHERE tb.".$tbprefix."EspecimenID=0";
	}
	@mysql_query($qq,$conn);
} 


$qq ="SELECT COUNT(*) as nn FROM ".$tbname." WHERE (".$tbprefix."EspecimenID+0)>0";
$ru = @mysql_query($qq,$conn);
$rwu = @mysql_fetch_assoc($ru);
$nspecids =  $rwu['nn']+0;

$qq ="SELECT COUNT(*) as nn FROM ".$tbname." WHERE ".$tbprefix."TaxonomiaIDs IS NOT NULL AND ".$tbprefix."TaxonomiaIDs<>''";
$ru = mysql_query($qq,$conn);
$rwu = mysql_fetch_assoc($ru);
$ntaxonomiaids =  $rwu['nn']+0;

$qq  = "SELECT COUNT(*) as nn FROM ".$tbname;
$ru = mysql_query($qq,$conn);
$rwu = mysql_fetch_assoc($ru);
$totalrecs = $rwu['nn']+0;
echo "
<br />
<table align='left' class='myformtable' cellpadding='5'>
<thead>
  <tr><td colspan='100%'>Checando link de identificação ou material testemunho!</td></tr>
</thead>
<tbody>";
$recsnolink = $totalrecs-$nspecids-$ntaxonomiaids ;
if ($recsnolink>0) {
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='left'>$recsnolink</td><td align='left'>registros sem identificação ou testemunho</td></tr>";
 } 
if ($nspecids>0) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='left'>$nspecids</td><td align='left' >Com material testemunho (identificacao ignorada)</td></tr>";
}
if ($ntaxonomiaids>0) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='left'>$ntaxonomiaids</td><td align='left' >Com identificação - No material testemunho</td></tr>";
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='left''>$totalrecs</td><td align='left'>TOTAL REGISTROS NO ARQUIVO</td></tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
if ($recsnolink>0) {
echo "
<form action='import-expedito-step02.php' method='post'>";
foreach ($_POST as $kk => $vv) {
		echo "
    <input type='hidden' name='".$kk."' value='".$vv."' />"; 
}
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center' ><input type='submit' value='Voltar' class='bblue'></td>
</tr>";

} else {
echo "
<form action='import-expedito-step08.php' method='post'>
";
foreach ($_POST as $kk => $vv) {
		echo "
    <input type='hidden' name='".$kk."' value='".$vv."' />"; 
}
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center' colspan='100%'><input type='submit' value='Continuar' class='bsubmit' /></td>
</tr>";
}
echo "
</form>
</tbody>
</table>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>