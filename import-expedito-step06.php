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
$title = 'Importar Expedito 06';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
$nnv = $_SESSION['fieldsign'];
$newv = unserialize($nnv);
if (!isset($testemuno)) {
	$colsign = array(
	"TESTEMUNHO_COLETOR",
	"TESTEMUNHO_NUMBERO");
	$pessoasvars = array();
	foreach ($colsign as $kk) {
		$datalev = trim($newv[$kk]);
		if (!empty($datalev)) {
			$testemuno[$kk] = $datalev;
		}
	}
} 
if (count($testemuno)==2) {
	$cln = $tbprefix."TESTEMUNHO_COLETOR";
	$cln2 = $tbprefix."TESTEMUNHO_NUMBERO";
	$orgnumcol = trim($newv["TESTEMUNHO_NUMBERO"]);
	$cll1 = $tbprefix."EspecimenID";
	if (!isset($testrunning)) {
		$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cln2." VARCHAR(100) DEFAULT ''";
		@mysql_query($qq,$conn);
		$qq = "UPDATE ".$tbname." SET `".$cln2."`=TRIM(`".$orgnumcol."`) where `".$orgnumcol."` IS NOT NULL AND `".$orgnumcol."`<>''";
		mysql_query($qq,$conn);
		$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cll1." INT(10) DEFAULT 0";
		@mysql_query($qq,$conn);
		$qq = "UPDATE `".$tbname."` as tb, `Especimenes` as pl SET tb.`".$cll1."`= pl.`EspecimenID` where tb.`".$cln."`= pl.ColetorID AND pl.`Number`=tb.`".$cln2."`";
		@mysql_query($qq,$conn);
	} else {
		$qq = "SELECT DISTINCT tb.`".$cln."`,tb.`".$cln2."` FROM `".$tbname."` as tb LEFT JOIN Pessoas as pe ON tb.$cln=pe.PessoaID  WHERE `".$cln."`>0 AND `".$cln2."` IS NOT NULL AND `".$cll1."`=0";
		$res = mysql_query($qq,$conn);
		$nres = mysql_numrows($res);
		$idx = 1;
		while ($rws = mysql_fetch_assoc($res)) {
				$spd = 'especimenid_'.$idx;
				$specid = $$spd;
				$qq = "UPDATE `".$tbname."` as tb SET tb.`".$cll1."`='".$specid."' WHERE tb.`".$cln."`='".$rws[$cln]."'  AND tb.`".$cln2."`='".$rws[$cln2]."' AND tb.`".$cll1."`=0";
				@mysql_query($qq,$conn);
				$idx++;
				//echo $qq."<br />";
		}
	}
	$qq = "SELECT DISTINCT pe.Abreviacao,tb.`".$cln2."` FROM `".$tbname."` as tb LEFT JOIN Pessoas as pe ON tb.$cln=pe.PessoaID  WHERE `".$cln."`>0 AND `".$cln2."` IS NOT NULL AND `".$cll1."`=0";
	$res = mysql_query($qq,$conn);
	$nres = mysql_numrows($res);
	if ($nres>0) {
		echo "
<br />
<table align='center' class='myformtable' cellpadding='5'>
<thead>
  <tr><td colspan='100%'>Problemas com material testemunho</td></tr>
  <tr class='subhead'>
    <td>Coletor</td>
    <td>Numero</td>
    <td>Pode ser</td>
  </tr>
</thead>
<tbody>
<form action='import-expedito-step06.php' method='post'>";
foreach ($_POST as $kk => $vv) {
	if (!empty($vv)) {
		echo "
  <input type='hidden' name='".$kk."' value='".$vv."'>"; 
		}
	}
$idx = 1;
while ($rws = mysql_fetch_assoc($res)) {
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td>".$rws['Abreviacao']."</td>
  <td>".$rws[$cln2]."</td>
  <td class='tdformnotes'>"; autosuggestfieldval('search-specimen.php','specname_'.$idx,' ','specnameres_'.$idx,'especimenid_'.$idx,true); echo "</td>
</tr>";
	$idx++;
}

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<input type='hidden' name='testrunning' value='".$nres."'>
<tr bgcolor = '".$bgcolor."'>
  <td align='center' colspan='100%'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
</form>
</tbody>
</table>";
		} else {
			$testem = 1;
			$testemunhodone =TRUE;
		}
} else {
	if (count($testemuno)>0) {
		echo "
<br />
<table align='center' class='myformtable' cellpadding='5'>
<thead>
  <tr><td colspan='100%'>Atenção</td></tr>
</thead>
<tbody>
<form action='import-expedito-step02.php' method='post'>";
foreach ($_POST as $kk => $vv) {
	if (!empty($vv)) {
		echo "
  <input type='hidden' name='".$kk."' value='".$vv."'>"; 
		}
	}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center' colspan='100%'>Você não indicou os identificadores de material testemunho para as amostras corretamente! Devem ser apenas 2 colunas, uma com o nome do coletor e outra com o numero da coleta!</td></tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center' colspan='100%'><input type='submit' value='".GetLangVar('namevoltar')."' class='bsubmit' /></td></tr>
</form>
</tbody>
</table>";
	} 
	else {
		$testem = 0;
		$testemunhodone=TRUE;
	}
}

$_SESSION['fieldsign'] = serialize($newv);
if ($testemunhodone) {
unset($_SESSION['taxafields']);
unset($_SESSION['fields']);
echo "
<form name='myform' action='import-expedito-step07.php' method='post'>
";
//coloca as variaveis anteriores
	foreach ($ppost as $kk => $vv) {
	echo "
  	<input type='hidden' name='".$kk."' value='".$vv."' />"; 
	}
echo "<input type='hidden' name='temtestemunho' value='".$testem."'>"; 
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