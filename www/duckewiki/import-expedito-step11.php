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
$title = 'Importar Expedito 11';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


$nnv = $_SESSION['fieldsign'];
$newv = unserialize($nnv);
$cadastrado=0;
$totalpontos=0;
$jaexiste=0;
$expedarr = array(
'ExpeditoID' => 'ExpeditoID',
'EspecimenIDs' => 'EspecimenID',
'TaxonomiaIDs' => 'TaxonomiaIDs',
'PessoasIDs' => 'PessoasIDs',
'IntervaloTempo' => 'IntervaloTempo');

$bafsf = array($tbprefix."ExpeditoID", $tbprefix."EspecimenID", $tbprefix."TaxonomiaIDs",$tbprefix."IntervaloTempo");
$babs2 = array($tbprefix."ExpeditoID as ExpeditoID", $tbprefix."EspecimenID as EspecimenIDs", $tbprefix."TaxonomiaIDs as TaxonomiaIDs",$tbprefix."IntervaloTempo as IntervaloTempo");
$basf = array();
$bab2 = array();
$qu = "SHOW FIELDS FROM `".$tbname."` WHERE Field LIKE '".$tbprefix."%'";
echo $qu."<br />";
$ru = mysql_query($qu,$conn);
while ($ruw = mysql_fetch_assoc($ru)) {
	$nr = $ruw['Field'];
	if (in_array($nr,$bafsf)) {
		$basf[] = $nr;
		$kk = array_search($nr,$bafsf);
		$bab2[] = $babs2[$kk];
	}
}


$bb = implode(", ",$basf);
$qq = "SELECT DISTINCT ".$bb." as pt, CONCAT(".$bb.") as idgrp FROM `".$tbname."`";
$rr = mysql_query($qq,$conn);
$totalpontos = mysql_numrows($rr);
while ($row = mysql_fetch_assoc($rr)) {
	$grp = $row['idgrp'];
	$bb2 = implode(", ",$bab2);
	$qq = "SELECT ".$bb2.", SUBSTRING(GROUP_CONCAT(
        CONCAT(';', ".$tbprefix."OBSERVADOR) SEPARATOR ''),2) as PessoasIDs FROM `".$tbname."` WHERE CONCAT(".$bb.")='".$grp."' LIMIT 0,1";
	$rer = mysql_query($qq,$conn);
	//echo $qq."<br />";
	while ($rew = mysql_fetch_assoc($rer)) {
		$ppid = explode(";",$rew['PessoasIDs']);
		//$ppid = array_unique($ppid);
		$rew['PessoasIDs'] = implode(";",$ppid);
		$arrayofvalues = $rew;
		//echopre($arrayofvalues);
		$expres = InsertIntoTable($arrayofvalues,'PlantaExpID','MetodoExpeditoPlantas',$conn);
		if ($expres) {
		   $cadastrado++;
		}
	}
}
if ($cadastrado==$totalpontos && $cadastrado>0) {
$qq = "SELECT count(*) as nrecs, count(distinct ".$tbprefix."ExpeditoID) as npots FROM `".$tbname."`";
$rr = mysql_query($qq,$conn);
$rw = mysql_fetch_assoc($rr);
$npots = $rw['npots'];
$nrecs = $rw['nrecs'];

$svv = trim($_SESSION['expeditoimporfile']);
$savetopathfile = "uploads/data_files/".$svv;
backup_tables($tbname,$savetopathfile,$conn);

echo "
<br />
<table align='left' class='myformtable' cellpadding='5'>
<thead>
  <tr><td colspan='100%'>Importação concluida</td></tr>
</thead>
<tbody>
<form >";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='left''>$nrecs</td><td align='left'>TOTAL DE LINHAS NO ARQUIVO</td></tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='left''>$cadastrado</td><td align='left'>TOTAL DE REGISTROS UNICOS (1 ESPECIE POR PONTO POR INTERVALO)</td></tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='left''>$npots</td><td align='left'>TOTAL DE PONTOS DE LEVANTAMENTO IMPORTADOS E CADASTRADOS</td></tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center' colspan='100%'><input type='submit' value='Fechar' class='bsubmit' onclick='javascript:window.close();'/></td></tr>
</form>
</tbody>
</table>";

} else {
$_SESSION['fieldsign'] = serialize($newv);
echo "
<br />
<table align='left' class='myformtable' cellpadding='5'>
<thead>
  <tr><td colspan='100%'>NÃO FOI POSSIVEL COMPLETAR A IMPORTAÇÃO</td></tr>
</thead>
<tbody>
<form >";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center' colspan='100%'><input type='submit' value='Fechar' class='bsubmit' onclick='javascript:window.close();' /></td></tr>
</form>
</tbody>
</table>";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>